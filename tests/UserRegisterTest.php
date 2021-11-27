<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\Data as Database;

final class UserRegisterTest extends TestCase {

    public function testUserCanBeCreated(): void {
        Database::create();
        $this->assertNotEmpty(Database::registerUser(239489823, "michyaraque", 'Michael', 'es', 'editor'));
    }
    
}
