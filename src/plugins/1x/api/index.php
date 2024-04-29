<?php 

include_once JWTPBM_PLUGIN_DIR.'/api/jwt_manager.php';
include_once JWTPBM_PLUGIN_DIR.'/api/db_tables.php';

add_action( 'rest_api_init', 'jwtpbm_register_api',10 );

function jwtpbm_register_api($wp_rest_server)
{   
    /*
    * @prefix jwtpbm ( JWT plugin By Mani )
    */
    $route_prefix = 'jwtpbm/v1';

    register_rest_route( $route_prefix, '/login', array(
        'methods'             => 'POST',
        'callback'            => 'jwtpbm_login',
        'permission_callback' => '__return_true',
        'args' => array(
            'username' => array(
                'type' => 'string',
                'required' => true,
                'description' => 'The user username or email address',
            ),
            'password' => array(
                'type' => 'string',
                'required' => true,
                'description' => 'The user password',
            ),
            
        ),
    ));

    register_rest_route($route_prefix, '/register', array(
        'methods' => 'POST',
        'callback' => 'jwtpbm_register_user',
        'permission_callback' => '__return_true',
        'args' => array(
            'email' => array(
                'type' => 'string',
                'required' => true,
                'description' => 'The user email address',
            ),
            'password' => array(
                'type' => 'string',
                'required' => true,
                'description' => 'The user password',
            ),
            'username' => array(
                'type' => 'string',
                'required' => true,
                'description' => 'The user username',
            )
        ),
    ));


}




function validateTokenDeprecated(WP_REST_Request $request)
{
    // $jwt    = $request->get_param('jwt');
    // $result = $this->validate($jwt);
    
    // if (!is_wp_error($result)) {
    //     $response = new WP_REST_Response($result);
    // } else {
    //     $response = new WP_REST_Response(array(
    //         'code'   => 'rest_jwt_validation_failure',
    //         'reason' => $result->get_error_message()
    //     ), 400);
    // }
    // return $response;;
}




function jwtpbm_register_user(WP_REST_Request $request) {

     $username    = $request->get_param('username');
     $password    = $request->get_param('password');
     $email    = $request->get_param('email');

    if (empty($username) || empty($password) || empty($email)) {
        return new WP_Error('missing_data', 'Username, password, and email are required', array('status' => 400));
    }

    if (username_exists($username) ) {
        return new WP_Error('user_exists', 'User with provided username already exists', array('status' => 409));  // 409 Conflict
    }

    if (email_exists($email)) {
        return new WP_Error('user_exists', 'User with provided  email already exists', array('status' => 400));
    }



    // Create user
    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        $error_string = $result->get_error_message();
        $error_code = array_key_first( $user_id->errors);
        return new WP_Error('user_create_error', "Error: {$error_string} , error_code {$error_code}", array('status' => 400));
    }

    // Return success message 
    return new WP_REST_Response(['status'=>1,'message' => 'User registered successfully', 'user_id' => $user_id], 200);
}


## Login function 
function jwtpbm_login(WP_REST_Request $request)
{
    $username = $request->get_param('username');
    $password = $request->get_param('password');

     // Basic validation
     if (empty($username) || empty($password)) {
        return new WP_Error('missing_data', 'Username or Email  and password are required', array('status' => 400));
     }

     $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL) !== false;
     if ($isEmail) {
        $user = get_user_by('email', $username);
     }

     if (!$isEmail) {
        $user = get_user_by('login', $username);
     }

     
     if (!$user) {
        return new WP_Error('invalid_credentials', 'Invalid username or password', array('status' => 401));
     }

    // Check if password is correct
     if (!wp_check_password($password, $user->user_pass)) {
        return new WP_Error('invalid_credentials', 'Invalid username or password', array('status' => 401));
     }

     JWTPBM_DbTables::create_refresh_tokens_table();
     
     $token_response = JWTPBM_JWTManager::getInstance()->generateLoginToken($user->ID);
     if($token_response['status'] == 0) {
        return new WP_Error('token_generation_failed', $token_response['message'], array('status' => 401));
     }

    // Return token
    return new WP_REST_Response($token_response, 200);
}