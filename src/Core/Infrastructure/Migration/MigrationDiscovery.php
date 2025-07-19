<?php

namespace Inquisition\Core\Infrastructure\Migration;

use Inquisition\Foundation\Config\Config;
use Inquisition\Foundation\Singleton\SingletonInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;
use ReflectionClass;
use RuntimeException;

final class MigrationDiscovery implements SingletonInterface
{
    private array $paths = [];

    use SingletonTrait;

    private function __construct() {
       $this->load();
    }

    private function load(): void {
        $paths = Config::getInstance()->get('migration.paths', $this->paths);
        if (empty($paths) || !is_array($paths)) {
            throw new RuntimeException('No migration paths defined in config. Set an array to "migration.paths"');
        }

        $paths = array_filter($paths, fn ($p) => is_string($paths));

        $this->paths = $paths;
    }

    /**
     * @return MigrationInterface[]
     */
    public function discover(): array
    {
        $migrations = [];
        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                throw new RuntimeException("Migration path does not exist: {$path}");
            }
            $files = glob($path . '/*.php');

            foreach ($files as $file) {
                $className = $this->getClassNameFromFile($file);

                if ($className && class_exists($className)) {
                    $reflection = new ReflectionClass($className);

                    if ($reflection->implementsInterface(MigrationInterface::class) &&
                        !$reflection->isAbstract()) {
                        $migrations[] = $className;
                    }
                }
            }
        }

        return $migrations;
    }

    /**
     * @param string $file
     *
     * @return string|null
     */
    private function getClassNameFromFile(string $file): ?string
    {
        $content = file_get_contents($file);

        if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches) &&
            preg_match('/class\s+(\w+)/', $content, $classMatches)) {
            return $namespaceMatches[1] . '\\' . $classMatches[1];
        }

        return null;
    }


}