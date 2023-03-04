<?php

class Main_model 
{
	private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

	public function insert_row($table, $data)
	{
		$key_string = "";
		$data_string = "";
		foreach ($data as $key => $value) {
			$key_string .=  (!empty($key_string) ? ", " : " ") . $key;
			$data_string .=  (!empty($data_string) ? ", " : "") . "'" . $value . "'";
		}

		$sql = "INSERT INTO $table($key_string) VALUES ($data_string)";

		if ($this->db->query($sql) === TRUE) {
			return $this->db->insert_id;
		} else {
			return 0;
		}
	}

	public function get_row($table, $where)
	{
		$where_string = "";
		foreach ($where as $key => $value) {
			$where_string .=  (!empty($where_string) ? "AND" : "") . " $key = '$value' ";
		}
		$where_string =  (!empty($where_string) ? " WHERE " : "") . $where_string;

		
		$sql = "SELECT * FROM $table" . $where_string;

		$result = $this->db->query($sql);
		while($row = mysqli_fetch_assoc($result)) {
			return $row;
		}
		return null;
	}

	public function update_row($table, $where, $data)
	{
		$where_string = "";
		foreach ($where as $key => $value) {
			$where_string .=  (!empty($where_string) ? "AND" : "") . " $key = '$value' ";
		}
		$where_string =  (!empty($where_string) ? " WHERE " : "") . $where_string;

		$data_string = "";
		foreach ($data as $key => $value) {
			$data_string .=  (!empty($data_string) ? "," : "") . " $key = '$value' ";
		}
		$data_string =  (!empty($data_string) ? " SET " : "") . $data_string;
		 $sql = "UPDATE $table" . $data_string  . $where_string;
		return $this->db->query($sql);
	}

	public function delete_row($table, $where)
	{
		$where_string = "";
		foreach ($where as $key => $value) {
			$where_string .=  (!empty($where_string) ? "AND" : "") . " $key = '$value' ";
		}
		$where_string =  (!empty($where_string) ? " WHERE " : "") . $where_string;
		$sql = "DELETE FROM $table" . $where_string;
		return $this->db->query($sql);
	}

	public function custom_query($query)
	{
		$result = $this->db->query($query);
		$return_array = [];
		while($row = mysqli_fetch_assoc($result)) {
			$return_array[] = $row;
		}
		return $return_array;
	}
}
