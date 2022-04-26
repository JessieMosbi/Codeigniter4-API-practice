<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MoveClientBasicTableToManageDatabase extends Migration
{
    public function up()
    {
        // student_api_manage DB 新增 client_basic
        // 把 member.client_basic 資料寫到 student_api_manage.client_basic
        // 刪除 member.client_basic

        $this->db->setDatabase("data_api_manage");
        $query = $this->db->query("
            CREATE TABLE IF NOT EXISTS client_basic LIKE member.client_basic
        ");

        $query = $this->db->query("
            INSERT client_basic
            SELECT * FROM member.client_basic
        ");

        $query = $this->db->query("
            DROP TABLE IF EXISTS member.client_basic
        ");
    }

    public function down()
    {
        // member DB 新增 client_basic
        // 把 student_api_manage.client_basic 資料寫到 member.client_basic
        // 刪除 student_api_manage.client_basic

        $this->db->setDatabase("data_api_manage");
        $query = $this->db->query("
            CREATE TABLE IF NOT EXISTS member.client_basic LIKE client_basic
        ");
        $query = $this->db->query("
            INSERT member.client_basic
            SELECT * FROM client_basic
        ");

        $query = $this->db->query("
            DROP TABLE IF EXISTS client_basic
        ");
    }
}
