<?php
use \Firebase\JWT\JWT;

class api {

    private $action;
    private $Main_model;

    public function __construct($db, $action)
    {
		$this->Main_model = new Main_model($db);
        $this->action = $action;
    }

	/**
    *
    * call function from action
    *
    * @param string $route
    * @param string $request
    * @param string $response
    * @return object
    *
    */
	public function processRequest()
    {
        switch ($this->action) {
        	case 'userRegister':
                $this->userRegister();
                break;
            case 'userLogin':
                $this->userLogin();
                break;
            case 'addTask':
                $this->addTask();
                break;
            case 'addNote':
                $this->addNote();
                break;
            case 'getTasks':
                $this->getTasks();
                break;
			case 'install':
				$this->install();
				break;
            default:
                $this->notFoundResponse();
                break;
        }
    }

	/**
    *
    * 404 not found return header
    *
    */
    public function notFoundResponse()
    {
        header('HTTP/1.1 404 Not Found');
    }

	/**
    *
    * User register
    *
    * @param string $email
    * @param string $password
    * @param string $name
    * @return object
    *
    */
    public function userRegister(){
		try {
			if(!empty($_POST['email']) && !empty($_POST['password']))
			{
				$data['email'] = $_POST['email'];
				$userdata = $this->Main_model->get_row("user", $data);
				$data['password'] = sha1($_POST['password']);
				$data['name'] = $_POST['name'];
				if(empty($userdata)){
					$data['created_at'] = date('Y-m-d H:i:s');  
					$insert = $this->Main_model->insert_row('user', $data);
					$json['status'] = true;
					$json['message'] = 'Successfully user registered';
				}
				else{
					$json['status'] = false;
					$json['message'] = 'Email is already exist on table';
				}
			}
			else{
				$json['status'] = false;
				$json['message'] = 'Please enter email and password';
			}
		} catch (Exception $e){
			$json['status'] = false;
			$json['message'] = $e->getMessage();
		}
		echo json_encode($json);exit;
	}

	/**
    *
    * User login
    *
    * @param string $email
    * @param string $password
    * @return object
    *
    */
	public function userLogin(){
		try {
			if (!empty($_POST['email']) && !empty($_POST['password'])) {
				$data['email'] = $_POST['email'];
				$user = $this->Main_model->get_row('user', $data);
				if ($user) {
					$data['password'] = sha1($_POST['password']);
					$user = $this->Main_model->get_row('user', $data);
					if ($user) {
						$iat = time(); // current timestamp value
						$exp = $iat + 21600;
						$payload = array(
							"iat" => $iat, //Time the JWT issued at
							"exp" => $exp, // Expiration time of token
							"uid" => $user['id'],
							"email" => $user['email']
						);
				
						$token = JWT::encode($payload, JWT_TOKEN_SECRET);

						$json['data'] = ['token' => $token, 'user' => $user];
						$json['status'] = true;
						$json['message'] = 'Login Successfully';
					} else {
						$json['status'] = false;
						$json['message'] = "Wrong password, try again!";
					}
				} else {
					$json['status'] = false;
					$json['message'] = "User does not exist.";
				}
			} else{
				$json['status'] = false;
				$json['message'] = 'Please enter email and password';
			}
		} catch (Exception $e){
			$json['status'] = false;
			$json['message'] = $e->getMessage();
		}

		echo json_encode($json);exit;
	}

	/**
    *
    * Validate call request
    *
    * @param array $validation_fields
    *
    */
	public function validate($validation_fields = [])
	{
		foreach ($validation_fields as $key => $value) {
			if(empty($_REQUEST[$value]))
			{
				$json['status'] = false;
				$json['message'] = 'Please enter ' . str_replace("_", " ", $value);
				echo json_encode($json);exit;
			}
		}
		return;
	}
	
	/**
    *
    * Add Task
    *
    * @param string $subject
    * @param string $start_date
    * @param string $due_date
    * @return object
    *
    */
    public function addTask() { 
		try {
			$this->auth_filter();
			$validation_fields = ["user_id","subject","start_date", "due_date"];
			$this->validate($validation_fields);
			
			$data['user_id'] = $_POST['user_id'];
			$data['subject'] = $_POST['subject'];
			$data['description'] = $_POST['description'];
			$data['start_date'] = $_POST['start_date'];
			$data['due_date'] = $_POST['due_date'];
			$data['status'] = $_POST['status'];
			$data['priority'] = $_POST['priority'];
			$data['created_at'] = date('Y-m-d H:i:s');   
			$insert = $this->Main_model->insert_row('task', $data);
			$json['data'] = ['id' => $insert];
			$json['status'] = true;
			$json['message'] = 'Add Successfully';
		} catch (Exception $e){
			$json['status'] = false;
			$json['message'] = $e->getMessage();
		}
		
		echo json_encode($json);exit;
	}

	/**
    *
    * Add Note
    *
    * @param integer $task_id
    * @param string $subject
    * @param string $note
    * @param string $due_date
    * @return object
    *
    */
	public function addNote(){ 
		try {
			$this->auth_filter();
			$validation_fields = ["task_id","subject", "note"];
			$this->validate($validation_fields);

			$data['task_id'] = $_POST['task_id'];
			$data['subject'] = $_POST['subject'];
			$data['note'] = $_POST['note'];
			$data['created_at'] = date('Y-m-d H:i:s');   
			$insert_id = $this->Main_model->insert_row('note', $data);
			if($insert_id){
				if (!empty($_FILES['file']['name'])) {
					$target_dir = "./attechments/";
					for ($i=0; $i<count($_FILES["file"]["name"]); $i++) {
						$target_file = $target_dir . basename($_FILES["file"]["name"][$i]);

						$adata['attachments'] = $_FILES['file']['name'][$i];
						$adata['created_at'] = date('Y-m-d H:i:s'); 
						move_uploaded_file($_FILES["file"]["tmp_name"][$i], $target_file); 
						$adata['note_id'] = $insert_id;
						$this->Main_model->insert_row('note_attachments', $adata);
					}
				}
			}
			$json['data'] = ['id' => $insert_id];
			$json['status'] = true;
			$json['message'] = 'Added Successfully';
		} catch (Exception $e){
			$json['status'] = false;
			$json['message'] = $e->getMessage();
		}
		echo json_encode($json);exit;
	}

	/**
    *
    * Get Tasks
    *
    * @return object
    *
    */
	public function getTasks(){
		try {
			$this->auth_filter();
			$validation_fields = ["user_id"];
			$this->validate($validation_fields);
			
			$user_id = $_GET['user_id'];

			$sql = "SELECT * FROM `task` where user_id = '$user_id' order by id desc";
			$result= $this->Main_model->custom_query($sql);
			$tasks = [];
			foreach($result as $keyRes => $valueRes) {
				print_r($valueRes); die;
				$tasks[] = $valueRes;
			}
			$parentData = [];
			foreach($tasks as $key => $parent){
				$newsql = "SELECT * FROM `note` where task_id=".$parent['id']." ";
				$newrecord= $this->Main_model->custom_query($newsql);
				$parentData[$key] = $parent;
				$parentData[$key]['notes'] = [];
				foreach($newrecord as $child){
					$sql = "SELECT * FROM `note_attachments` where note_id=".$child['id']." ";
					$note_attachments = $this->Main_model->custom_query($sql);
					$child['note_attachments'] = [];
					foreach($note_attachments as $note_attachment){
						$child['note_attachments'][] = PROJECT_BASE_PATH . "/attechments/" . $note_attachment['attachments'];
					}
					$parentData[$key]['notes'][] = $child;
				}
				$json['status'] = true;
				$json['message'] = '';
			}
			$json['data'] = $parentData;
		} catch (Exception $e){
			$json['status'] = false;
			$json['message'] = $e->getMessage();
		}
		echo json_encode($json);exit;
	}

	/**
    *
    * Check authorization
    *
    * @return object
    *
    */
    public function auth_filter() {
        try {
			$headers = apache_request_headers();
            if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                header('HTTP/1.0 400 Bad Request');
                $json['status'] = false;
                $json['message'] = "Token not found in request"; 
                echo json_encode($json);exit;
            }
            
            $jwt = $matches[1];
            if (empty($jwt)) {
                $json['status'] = false;
                $json['message'] = "No token was able to be extracted from the authorization header"; 
                header('HTTP/1.0 400 Bad Request');
                echo json_encode($json);exit;
            }
            
            $secret_Key  = JWT_TOKEN_SECRET;
            $token = JWT::decode($jwt, $secret_Key, ['HS256']);
            
            if(is_string($token)) {
				header('HTTP/1.1 401 Unauthorized');
                if($token == "Expired") {
                    $json['status'] = false;
                    $json['message'] = "Token is expired!"; 
                } else {
                    $json['status'] = false;
                    $json['message'] = $token; 
                }
                
            } else {
                return true;
            }
            
            echo json_encode($json);exit;
        }
        catch(Exception $e) {
			header('HTTP/1.1 401 Unauthorized');
            $json['status'] = false;
            $json['message'] = $e->getMessage();
            echo json_encode($json);exit;
        }
    }
}