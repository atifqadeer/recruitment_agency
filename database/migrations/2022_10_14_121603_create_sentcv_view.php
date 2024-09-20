<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSentcvView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('sentcv_view', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->timestamps();
        // });
        \DB::statement($this->createView());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('sentcv_view');
        \DB::statement($this->dropView());
    }


    private function createView(): string
    {
        return <<<SQL
            CREATE VIEW view_sentcv_data AS
            SELECT 
            crm_notes.sales_id, crm_notes.applicant_id as app_id, crm_notes.details as crm_note_details, crm_notes.crm_added_date, crm_notes.crm_added_time from applicants inner join crm_notes on applicants.id = crm_notes.applicant_id where crm_notes.moved_tab_to in ('cv_sent', 'cv_sent_saved') order by crm_notes.id desc
            SQL;
    }
   
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    private function dropView(): string
    {
        return <<<SQL

            DROP VIEW IF EXISTS `view_sentcv_data`;
            SQL;
    }
}
