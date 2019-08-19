<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompassRouteablesTable extends Migration
{
    /**
     * The database schema.
     *
     * @var Schema
     */
    protected $schema;

    /**
     * Create a new migration instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->schema = Schema::connection(
            config('compass.storage.database.connection')
        );
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('compass_routeables', function (Blueprint $table) {
            $table->uuid('uuid')->unique();
            $table->string('route_hash');
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->boolean('docs')->default(false);
            $table->timestamps();

            $table->index(['route_hash', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('compass_routeables');
    }
}