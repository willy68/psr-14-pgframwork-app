# PgFramework Development Guidelines

This document provides essential information for developers working on this PSR-14 PgFramework application.

## Build/Configuration Instructions

### Environment Setup

The application uses Symfony's Dotenv component to manage environment variables. Two main environment files are used:

- `.env`: Main environment configuration
- `.env.local`: Local overrides (not committed to version control)

Template files (`.env.dist` and `.env.local.dist`) are provided and copied automatically during installation.

### Installation

```bash
# Clone the repository
git clone <repository-url>
cd psr-14-pgframwork-app

# Install dependencies
composer install

# Generate application key
./bin/console key:generate
```

### Database Configuration

Database settings are configured in `config/database.php` and use environment variables from `.env` files:

```
DATABASE_SGDB=mysql
DATABASE_HOST=localhost
DATABASE_USER=root
DATABASE_PASSWORD=your_password
DATABASE_NAME=framework
```

The application supports multiple database connections:
- ActiveRecord connections
- Doctrine ORM connections

### Database Setup

```bash
# Create database and run migrations
./bin/console doctrine:database:create --if-not-exists
./bin/console migrations:migrate --no-interaction

# Load fixtures (sample data)
./bin/console fixtures:load --append app/Blog/db/Fixtures app/Auth/db/Fixtures
```

You can also use the composer script:

```bash
composer database-clean
```

## Testing Information

### Testing Framework

The application uses PHPUnit for testing. The configuration is in `phpunit.xml` at the project root.

### Running Tests

```bash
# Run all tests
./bin/phpunit

# Run a specific test file
./bin/phpunit tests/Path/To/TestFile.php

# Run tests in a specific directory
./bin/phpunit tests/Framework/

# Run tests with specific group
./bin/phpunit --group=groupname
```

### Creating Tests

1. Create test classes in the `tests/` directory, mirroring the structure of the application code
2. Test classes should:
   - Be in the `Tests\` namespace
   - Extend `PHPUnit\Framework\TestCase` or a specialized test case class
   - Have method names prefixed with `test`
   - Use assertions to verify expected behavior

### Example Test

Here's a simple test example:

```php
<?php

declare(strict_types=1);

namespace Tests\Demo\Utils;

use App\Demo\Utils\StringUtils;
use PHPUnit\Framework\TestCase;

class StringUtilsTest extends TestCase
{
    public function testReverse(): void
    {
        $this->assertEquals('olleh', StringUtils::reverse('hello'));
    }

    public function testIsPalindrome(): void
    {
        $this->assertTrue(StringUtils::isPalindrome('radar'));
        $this->assertFalse(StringUtils::isPalindrome('hello'));
    }
}
```

### Database Testing

For tests requiring a database, extend the `Tests\DatabaseTestCase` class which provides:

- In-memory SQLite database setup
- Migration and seeding methods
- PDO connection management

Example:

```php
<?php

namespace Tests\Blog;

use Tests\DatabaseTestCase;

class PostTest extends DatabaseTestCase
{
    private $pdo;
    
    public function setUp(): void
    {
        $this->pdo = $this->getPDO();
        $this->migrateDatabase($this->pdo);
        // Optional: $this->seedDatabase($this->pdo);
    }
    
    public function testFindPost(): void
    {
        // Test database operations
    }
}
```

## Application Architecture

### Modular Structure

The application follows a modular architecture:

- `app/`: Application modules (Auth, Blog, Admin, etc.)
- `PgFramework/`: Core framework components
- `config/`: Configuration files
- `public/`: Public-facing files and entry point

### Key Components

1. **Middleware Pipeline**: PSR-15 middleware for request processing
2. **Event System**: PSR-14 event dispatcher for application events
3. **Dependency Injection**: PHP-DI for service container
4. **ORM**: Supports both Doctrine ORM and ActiveRecord
5. **Templating**: Twig templating engine

### Bootstrap Process

The application bootstrap process:

1. Load environment variables
2. Create App instance
3. Register modules
4. Register event listeners
5. Initialize the application
6. Process the request through middleware pipeline

## Development Guidelines

### Coding Standards

The project follows PSR-12 coding standards. Code style is checked using PHP_CodeSniffer:

```bash
./bin/phpcs
```

The configuration is in `phpcs.xml`.

### Adding New Modules

1. Create a new directory in `app/`
2. Create a module class that registers routes, dependencies, etc.
3. Add the module to the list in `app/Bootstrap/App.php`

### Event Listeners

Event listeners are registered in `app/Bootstrap/App.php` with priority values. Higher priority listeners are executed first.

### Middleware

Middleware can be added:
- Globally in `app/Bootstrap/App.php`
- To specific routes or route groups

### Security

The application uses a firewall system for authentication and authorization. Security configuration is in `config/firewall.php`.

## Debugging

For development, the application includes:
- Whoops error handler
- PHP DebugBar
- Symfony VarDumper

Enable debug mode by setting `APP_ENV=dev` in your `.env` file.