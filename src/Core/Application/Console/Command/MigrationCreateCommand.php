<?php

namespace Inquisition\Core\Application\Console\Command;

use DateTimeImmutable;
use Inquisition\Core\Infrastructure\Migration\MigrationDiscovery;
use InvalidArgumentException;
use RuntimeException;

class MigrationCreateCommand extends AbstractCommand
{
    private const string ARGUMENT_NAME = 'name';
    private const string ARGUMENT_PATH = 'path';
    private const string ARGUMENT_PATH_LIST = 'path_list';

    private MigrationDiscovery $migrationDiscovery;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
        $this->migrationDiscovery = MigrationDiscovery::getInstance();
    }

    /**
     * @return string
     */
    public static function getAlias(): string
    {
        return 'migration:create';
    }

    /**
     * @return string[]
     */
    public static function getArguments(): array
    {
        return [
            self::ARGUMENT_NAME => 'Migration name',
            self::ARGUMENT_PATH => 'Name of the migration path from the config database.migration.paths',
            self::ARGUMENT_PATH_LIST => 'List of migration paths from the config database.migration.paths',
        ];
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Create a new migration';
    }

    /**
     * @return string
     */
    public function getHelp(): string
    {
        return 'Create a new migration';
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->validate();

        $this->createMigration();
    }

    /**
     * @return void
     */
    private function validate(): void
    {
        if (empty($this->migrationDiscovery->paths)) {
            throw new RuntimeException('No migration paths defined in config. Set an array to "database.migration.paths"');
        }

        if (array_key_exists(self::ARGUMENT_PATH_LIST, $this->parameters)) {
            $this->showPaths();

            return;
        }

        $missing = [];

        if (!array_key_exists(self::ARGUMENT_PATH, $this->parameters)) {
            $missing[] = self::ARGUMENT_PATH;
        }

        if (!array_key_exists(self::ARGUMENT_NAME, $this->parameters)) {
            $missing[] = self::ARGUMENT_NAME;
        }

        if (!empty($missing)) {
            throw new RuntimeException('Missing required arguments: ' . implode(', ', $missing));
        }

        $pathName = $this->parameters[self::ARGUMENT_PATH];
        if (!array_key_exists($pathName, $this->migrationDiscovery->paths)) {
            throw new RuntimeException('Invalid path: ' . $pathName . ' use one of: '
                . implode(', ', array_keys($this->migrationDiscovery->paths)));
        }
    }

    /**
     * @return void
     */
    private function createMigration(): void
    {
        $dir = $this->migrationDiscovery->paths[$this->parameters[self::ARGUMENT_PATH]];
        $namespace = $this->pathToNamespaceFromComposer($dir);
        $className = $this->getClassName();
        $path = str_replace('/', DIRECTORY_SEPARATOR,$dir . '/' . $className . '.php');

        if (file_exists($path)) {
            throw new RuntimeException("Migration file already exists: $path");
        }

        $template = <<<PHP
<?php

namespace $namespace;

use Inquisition\Core\Infrastructure\Migration\AbstractMigration;

final readonly class $className extends AbstractMigration
{

    public function getVersion(): string
    {
        // TODO: Implement getVersion() method.
    }

    public function getDescription(): string
    {
        // TODO: Implement getDescription() method.
    }

    public function up(): void
    {
        // TODO: Implement up() method.
    }

    public function down(): void
    {
        // TODO: Implement down() method.
    }
}
PHP;
        if (!file_exists($dir)) {
            mkdir($dir, 0774, true);
        }
        file_put_contents($path, $template);
        chmod($path, 0664);
        echo "Migration file created: $path\n";
    }

    /**
     * @return string
     */
    private function getClassName(): string
    {
        $dateTime = new DateTimeImmutable();
        $postFix = $dateTime->format('_Ymd_His');

        return $this->parameters[self::ARGUMENT_NAME] . $postFix;
    }

    /**
     * @return void
     */
    private function showPaths(): void
    {
        echo "Available migration paths:\n";
        foreach ($this->migrationDiscovery->paths as $path) {
            echo "\t$path\n";
        }
    }

    /**
     * @return array
     */
    private function getComposerPsr4Mappings(): array
    {
        $composerJsonPath = 'composer.json';

        if (!file_exists($composerJsonPath)) {
            throw new InvalidArgumentException("composer.json not found at: $composerJsonPath");
        }

        $composer = json_decode(file_get_contents($composerJsonPath), true);

        return $composer['autoload']['psr-4'] ?? [];
    }

    /**
     * @param string $filePath
     * @return string|null
     */
    private function pathToNamespaceFromComposer(string $filePath): ?string
    {
        $psr4Mappings = $this->getComposerPsr4Mappings();

        return $this->pathToNamespace($filePath, $psr4Mappings);
    }

    /**
     * @param string $filePath
     * @param array $psr4Mappings
     * @return string|null
     */
    private function pathToNamespace(string $filePath, array $psr4Mappings): ?string
    {
        $filePath = trim($filePath, '/\\');

        foreach ($psr4Mappings as $namespace => $basePath) {
            $basePath = trim($basePath, '/\\');

            if (str_starts_with($filePath, $basePath)) {
                $relativePath = substr($filePath, strlen($basePath));
                $relativePath = trim($relativePath, '/\\');

                $namespaceSuffix = str_replace(['/', '\\'], '\\', $relativePath);

                return rtrim($namespace, '\\') . '\\' . $namespaceSuffix;
            }
        }

        return null;
    }
}