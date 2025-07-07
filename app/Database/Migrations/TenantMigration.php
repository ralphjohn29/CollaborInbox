<?php

namespace App\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

abstract class TenantMigration extends Migration
{
    /**
     * Add tenant_id foreign key to the given table
     *
     * @param Blueprint $table
     * @param bool $withIndex Whether to add an index on tenant_id
     * @param bool $withForeignKey Whether to add a foreign key constraint
     * @return void
     */
    protected function addTenantId(Blueprint $table, bool $withIndex = true, bool $withForeignKey = false): void
    {
        // Using string for tenant ID to match the stancl/tenancy implementation
        $table->string('tenant_id');
        
        // Add index for better performance
        if ($withIndex) {
            $table->index('tenant_id');
        }
        
        // Add foreign key constraint
        // Only applies in single-database tenancy; for multi-database tenancy this is not needed
        if ($withForeignKey) {
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        }
    }
    
    /**
     * Create a new table with tenant_id column
     *
     * @param string $tableName
     * @param callable $callback
     * @param bool $withIndex Whether to add an index on tenant_id
     * @param bool $withForeignKey Whether to add a foreign key constraint
     * @return void
     */
    protected function createTableWithTenantId(string $tableName, callable $callback, bool $withIndex = true, bool $withForeignKey = false): void
    {
        Schema::create($tableName, function (Blueprint $table) use ($callback, $withIndex, $withForeignKey) {
            // Add tenant ID first
            $this->addTenantId($table, $withIndex, $withForeignKey);
            
            // Let the child migration define the rest of the schema
            $callback($table);
        });
    }
    
    /**
     * Add tenant_id to an existing table
     *
     * @param string $tableName
     * @param bool $withIndex Whether to add an index on tenant_id
     * @param bool $withForeignKey Whether to add a foreign key constraint
     * @return void
     */
    protected function addTenantIdToTable(string $tableName, bool $withIndex = true, bool $withForeignKey = false): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($withIndex, $withForeignKey) {
            $this->addTenantId($table, $withIndex, $withForeignKey);
        });
    }
    
    /**
     * Create a foreign key reference to another tenant-scoped table
     * This ensures that the foreign key relationship respects tenant boundaries
     *
     * @param Blueprint $table
     * @param string $column
     * @param string $referencedTable
     * @param string $referencedColumn
     * @param string $onDelete
     * @return void
     */
    protected function tenantForeignKey(Blueprint $table, string $column, string $referencedTable, string $referencedColumn = 'id', string $onDelete = 'cascade'): void
    {
        // Add the foreign key column if it doesn't exist
        if (!Schema::hasColumn($table->getTable(), $column)) {
            if ($referencedColumn === 'id') {
                $table->foreignId($column);
            } else {
                $table->string($column);
            }
        }
        
        // Add a composite foreign key that includes tenant_id to ensure cross-tenant data integrity
        $table->foreign([$column, 'tenant_id'])
            ->references([$referencedColumn, 'tenant_id'])
            ->on($referencedTable)
            ->onDelete($onDelete);
    }
} 