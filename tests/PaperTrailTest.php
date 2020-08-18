<?php

namespace Logispot\PaperTrail\Tests;

use Logispot\PaperTrail\Tests\Models\Product;
use Logispot\PaperTrail\Models\PaperTrail;

class PaperTrailTest
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->loadLaravelMigrations(['--database' => 'testing']);

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => realpath(__DIR__.'/../src/migrations'),
        ]);

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => realpath(__DIR__.'/../tests/migrations'),
        ]);
    }

    public function testUsersTable()
    {
        Product::create([
            'product_name' => 'Chair',
            'amount' => 1,
            'price' => 100,
        ]);

        $product = Product::findOrFail(1);
        $this->assertEquals('Chair', $product->product_name);
        $this->assertEquals(1, $product->amount);
        $this->assertEquals(100, $product->price);
    }

    public function testRevisionsStored()
    {
        $product = Product::create([
            'product_name' => 'Chair',
            'amount' => 1,
            'price' => 100,
        ]);

        $product->update([
            'amount' => 2
        ]);

        $paperTrail = PaperTrail::findOrFail(1);
        $this->assertEquals('amount', $paperTrail->key);
        $this->assertEquals(1, $paperTrail->old_value);
        $this->assertEquals(2, $paperTrail->new_value);

        $product->update([
            'price' => 200
        ]);

        $product = PaperTrail::findOrFail(2);
        $this->assertEquals('price', $paperTrail->key);
        $this->assertEquals(100, $paperTrail->old_value);
        $this->assertEquals(200, $paperTrail->new_value);

        $this->assertCount(2, $product->paperTrails);
    }
}
