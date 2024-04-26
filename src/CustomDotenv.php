<?php

use vlucas\phpdotenv;

use Dotenv\Dotenv;
use Dotenv\Store\StoreInterface;

use Dotenv\Exception\InvalidEncodingException;
use Dotenv\Exception\InvalidPathException;

//use Dotenv\Store\FileStore;
use Dotenv\Loader\LoaderInterface;
//use Dotenv\Store;

class CustomFileStore implements StoreInterface
{
    protected $filePaths;
    protected $immutable;

    public function __construct(array $filePaths, bool $immutable = false)
    {
        $this->filePaths = $filePaths;
        $this->immutable = $immutable;
    }

    public function read(): string
    {
        $contents = '';

        foreach ($this->filePaths as $filePath) {
            if (is_readable($filePath)) {
                $contents .= file_get_contents($filePath) . PHP_EOL;
            } else {
                throw new InvalidPathException("File path '$filePath' is not readable.");
            }
        }

        return $contents;
    }

    public function getFilePath(): string
    {
        return $this->filePaths[0]; // Assuming there is only one file path
    }
}

use Dotenv\Repository\RepositoryInterface;

class CustomRepository implements RepositoryInterface
{
    protected $variables = [];

    public function get(string $name): ?string
    {
        return $this->variables[$name] ?? null;
    }

    public function set(string $name, string $value): void
    {
        $this->variables[$name] = $value;
    }

    public function clear(string $name): void
    {
        unset($this->variables[$name]);
    }

    public function all(): array
    {
        return $this->variables;
    }

    public function has(string $name): bool
    {
        return isset($this->variables[$name]);
    }
}

class CustomLoader implements LoaderInterface
{
    public function load(RepositoryInterface $repository, array $entries): array
    {
        foreach ($entries as $name => $value) {
            $repository->set($name, $value);
        }
        // Implement your custom loading logic here
        // For simplicity, we'll just return an empty array
        return $repository->all();
    }
}

use Dotenv\Parser\ParserInterface;

class CustomParser implements ParserInterface
{
    public function parse(string $data): array
    {
        $lines = explode("\n", $data);
        $entries = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $entries[$name] = $value;
            }
        }
        return $entries;
    }
}

class CustomDotenv extends Dotenv
{
    protected $store;
    protected $parser;
    protected $loader;
    protected $repository;

    public function __construct(StoreInterface $store, ParserInterface $parser, LoaderInterface $loader, RepositoryInterface $repository)
    {
        parent::__construct($store, $parser, $loader, $repository);
        $this->store = $store;
        $this->parser = $parser;
        $this->loader = $loader;
        $this->repository = $repository;
    }

    public function load(): array
    {
        $envFilePath = $this->store->getFilePath(); // Get the file path from the store

        if (!file_exists($envFilePath) || !is_readable($envFilePath)) {
            throw new \RuntimeException("The environment file \"$envFilePath\" is not readable.");
        }

        $envContent = file_get_contents($envFilePath); // $this->store->read();

        $entries = $this->loader->load($this->repository, $this->parser->parse($envContent));

        foreach ($entries as $name => $value) {
           $_ENV[$name] = $value;
        }

/*
        foreach ($entries as $entry) {
            if (is_array($entry) && count($entry) >= 2) {
                list($name, $value) = $this->loader->load($this->repository, [$entry]);
                putenv("$name=$value");
                $_ENV[$name] = $value;
            }
        }
*/
        return $entries;
    }

    public function set(string $name, string $value): void
    {
        $this->repository->set($name, $value); // parent::set($name, $value);
        putenv("$name=$value"); // Update the environment variable
        $_ENV[$name] = $value; // Update $_ENV as well
    }
    
    public function save()
    {
        $envFilePath = $this->store->getFilePath(); // Get the file path from the store

            if (!file_exists($envFilePath) || !is_writable($envFilePath)) {
            return false;
        }

        // Read existing environment variables from the .env file
        $existingEnvContent = file_get_contents($envFilePath);
        $existingEnvVariables = $this->parser->parse($existingEnvContent);

        // Merge existing environment variables with repository variables
        $allVariables = array_merge($existingEnvVariables, $this->repository->all());

        $envContent = '';
        foreach ($allVariables as $name => $value) {
            $envContent .= $name . '=' . $this->quoteValue($value) . PHP_EOL;
        }

        // Save the environment variables to the .env file
        $result = (bool)file_put_contents($envFilePath, $envContent);

        // Load the environment variables back into $_ENV
        $this->loadEnv();

        return $result;
    }

    protected function loadEnv()
    {
        foreach ($this->repository->all() as $name => $value) {
            $_ENV[$name] = $value;
        }
    }

    protected function quoteValue($value): string
    {
        if ($this->needsQuotes($value) && preg_match('/^\".*\"$/', $value)) {
            return $value; // addcslashes($value, '"')
        }

        return $value;
    }

    protected function needsQuotes($value): bool
    {
        return strpos($value, ' ') !== false || strpos($value, '#') !== false || strpos($value, '"') !== false;
    }

    protected function loadExistingEnv(): array
    {
        $existingEnv = [];
        foreach ($_ENV as $name => $value) {
            $existingEnv[$name] = $value;
        }
        return $existingEnv;
    }
}
?>