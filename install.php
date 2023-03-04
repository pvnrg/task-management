<?php
require "config.php";

class install {

    /**
    *
    * create database and table
    *
    * @return object
    *
    */
    public function create_database_and_tables()
    {
        try {
            // Create connection
            $conn = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD);
            // Check connection
            $this->check_database_connection($conn);

            // Create database
            $sql = "CREATE DATABASE IF NOT EXISTS " . DATABASE_NAME;
            if ($conn->query($sql) === TRUE) {

                // connect database
                $conn = new \mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);
                // Check connection
                $this->check_database_connection($conn);

                // get tables list with create table query
                $tables = $this->get_tables();

                // define success message and status
                $json['status'] = true;
                $json['message'] = "Created database and tables successfully";

                foreach ($tables as $table => $create_table_sql) {
                    // check table exist
                    $sql = "SHOW TABLES LIKE '".$table."'";
                    $data = $conn->query($sql);

                    // if table exist drop a table
                    if(isset($data->num_rows) && $data->num_rows > 0) {
                        $sql = "DROP TABLE ".$table;
                        $conn->query($sql);
                    }

                    // create new table
                    if ($conn->query($create_table_sql) !== TRUE) { // if any error than store in creating_table_error
                        $json['status'] = false;
                        $json['creating_table_error'][$table] = "Error creating table: " . $conn->error;
                    }
                }
            } else {
                $json['status'] = false;
                $json['message'] = "Error creating database: " . $conn->error;
            }
            $conn->close();
        } catch (Exception $e){
            $json['status'] = false;
            $json['message'] = $e->getMessage();
        }
        echo json_encode($json);exit;
    }

    /**
    *
    * check database connection
    *
    */
    public function check_database_connection($conn)
    {
        if ($conn->connect_error) {
            $json['status'] = false;
            $json['message'] = "Connection failed: " . $conn->connect_error;
            echo json_encode($json);exit;
        }
    }

    /**
    *
    * get tables list with create table sql
    *
    * @return array
    *
    */
    public function get_tables()
    {
        return [
            "user" => "CREATE TABLE user (
                id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL
            )",
            "task" => "CREATE TABLE task (
                id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT(255) NULL,
                subject VARCHAR(255) NULL,
                description TEXT NULL,
                start_date DATE NULL,
                due_date DATE NULL,
                status ENUM('New', 'Incomplete', 'Complete') DEFAULT 'New',
                priority ENUM('High', 'Medium', 'Low') DEFAULT 'Low',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL
            )",
            "note" => "CREATE TABLE note (
                id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                task_id INT(255) NULL,
                subject VARCHAR(255) NULL,
                note TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL
            )",
            "note_attachments" => "CREATE TABLE note_attachments (
                id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                note_id INT(255) NULL,
                attachments TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL
            )",
        ];
    }

}

$controller = new install();
$controller->create_database_and_tables();