<?php

return [
    /*
     * If false, no papertrail will be recorded.
     */
    'enabled' => env('PAPER_TRAIL_ENABLED', true),

    /*
     * This model will be used to record paper trail.
     * It should be implements the Logispot\PaperTrail\Models\PaperTrail interface
     * and extend Illuminate\Database\Eloquent\Model.
     */
    'model' => \Logispot\PaperTrail\Models\PaperTrail::class,

    /*
     * This is the name of the table that will be created by the migration and
     * used by the PaperTrail model shipped with this package.
     */
    'table_name' => 'paper_trails',

    /*
     * This is the database connection that will be used by the migration and
     * the PaperTrail model shipped with this package. In case it's not set
     * Laravel database.default will be used instead.
     */
    'database_connection' => env('ACTIVITY_LOGGER_DB_CONNECTION'),
];
