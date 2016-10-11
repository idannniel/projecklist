<?php
/**
 * File name: functions.php
 *
 * This file is part of PROJECKLIST
 *
 * @author Daniel Racine <mailto.danielracine@gmail.com>
 * @link --
 * @package PROJECKLIST
 * @version 1
 *
 * Copyright (c) 2015 Daniel Racine
 * You should have received a copy of the MIT License
 * along with PROJECKLIST. If not, see <https://en.wikipedia.org/wiki/MIT_License>.
 */




    require_once("config.php");

    /**
     * Apologizes to user with message.
     */
    function apologize($message)
    {
        render("_apology.php", "Ouppps!", ["message" => $message]);
    }

    /**
     * Facilitates debugging by dumping contents of argument(s)
     * to browser.
     */
    function dump()
    {
        $arguments = func_get_args();
        require("../views/_dump.php");
        exit;
    }

    /**
     * Logs out current user, if any.  Based on Example #1 at
     * http://us.php.net/manual/en/function.session-destroy.php.
     */
    function logout()
    {
        // Store DEV server root
        $server_root = $_SESSION["server_root"];

        // unset any session variables
        $_SESSION = [];

        // expire cookie
        if (!empty($_COOKIE[session_name()]))
        {
            setcookie(session_name(), "", time() - 42000);
        }

        // destroy session
        session_destroy();

        // redirect to server root
        redirect($server_root."/");
    }

    /**
     * Redirects user to location, which can be a URL or
     * a relative path on the local host.
     *
     * http://stackoverflow.com/a/25643550/5156190
     *
     * Because this function outputs an HTTP header, it
     * must be called before caller outputs any HTML.
     */
    function redirect($location)
    {
        if (headers_sent($file, $line))
        {
            trigger_error("HTTP headers already sent at {$file}:{$line}", E_USER_ERROR);
        }

        // dev environemnt root
        if (isset($_SESSION["server_root"]))
        {
            $redirect = $_SESSION["server_root"] . $location;
        }
        else
        {
            $redirect = $location;   
        }

        header("Location: {$redirect}", true);
        // echo "<script>window.top.location='". $redirect ."'</script>";
        exit;
    }

    /**
     * Renders view, passing in values.
     */
    function render($view, $pagename, $values = [])
    {
        // if view exists, render it
        if (file_exists("../views/{$view}"))
        {
            $s_displayname = empty($_SESSION["id"]) ? "<a class=\"login_link\" href=\"login.php\">Sign in</a><span class=\"register_link\"> or <a href=\"register.php\">Register</a></span>" : $_SESSION["user_name"];
            
            $_SESSION["view"] = substr($view, 0, -4);
            $title = $pagename;
            
            // extract variables into local scope
            extract($values);

            // render view (between header and footer)
            require("../views/header.php");
            require("../views/{$view}");
            require("../views/footer.php");
            exit;
        }

        // else err
        else
        {
            trigger_error("Invalid view: {$view}", E_USER_ERROR);
        }
    }




    #########################################################
    # Copyright © 2008 Darrin Yeager                        #
    # https://www.dyeager.org/                               #
    # Licensed under BSD license.                           #
    # https://www.dyeager.org/downloads/license-bsd.txt    #
    #########################################################
    function parseDefaultLanguage($http_accept, $deflang = "en") {
       if(isset($http_accept) && strlen($http_accept) > 1)  {
          # Split possible languages into array
          $x = explode(",",$http_accept);
          foreach ($x as $val) {
             #check for q-value and create associative array. No q-value means 1 by rule
             if(preg_match("/(.*);q=([0-1]{0,1}.\d{0,4})/i",$val,$matches))
                $lang[$matches[1]] = (float)$matches[2];
             else
                $lang[$val] = 1.0;
          }

          #return default language (highest q-value)
          $qval = 0.0;
          foreach ($lang as $key => $value) {
             if ($value > $qval) {
                $qval = (float)$value;
                $deflang = $key;
             }
          }
       }
       return strtolower($deflang);
    }




    function getDefaultLanguage() {
       if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
          return parseDefaultLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
       else
          return parseDefaultLanguage(NULL);
    }




    function setLanguage($lang) {

        // Extract language value from browser $locale
        $re_lang = '/^\p{L}{1,2}/u';
        if (preg_match($re_lang, $lang, $match))
        {
            $lang_code = $match[0];
        }
        else
        {
            $lang_code = "en";
        }

        // Extract locale value from browser $locale
        $re_locale = '/(?<=\p{Pd})\p{L}+$/u';
        if (preg_match($re_locale, $lang, $match))
        {
            $locale_code = $match[0];
        }
        else
        {
            $locale_code = "CA";
        }

        // Check if L18N exist for user locale, if not default to $default_locale
        $po_lang = $lang_code."_".$locale_code;
        if ( !in_array( $po_lang, $_SESSION['form_PO_support'] ) )
        {
            $po_lang = $_SESSION['default_locale'];
        }

        // register the session and set the cookie
        $_SESSION['lang'] = $po_lang;
        $_SESSION['htmllang'] = $lang;

        // PO I18N support information here
        putenv("LANG=".$_SESSION['lang']);
        setlocale(LC_ALL, $_SESSION['lang']);

    }




    function setLanguageMenu() {

    	if (isset($_SESSION['form_PO_support']) && count($_SESSION['form_PO_support']) != 0)
    	{
    		$codeBloc = "<ul class=\"is-not-toggled\">";
            
            // // Debug Language Menu
            //     $codeBloc .=        "<li>";
            //     $codeBloc .=            "<a href=\"?lang=" . "en_CA" . "\">"."Checkoslovac ( Comrad Testing )"."<span class=\"fa fa-globe fa-lg\"></span>"."</a>";
            //     $codeBloc .=        "</li>";

    		foreach ($_SESSION['form_PO_support'] as $display => $code) {
    			$codeBloc .= "<li>";
    			$codeBloc .= "<a href=\"?lang=" . $code . "\">" . $display . "<span class=\"fa fa-globe fa-lg\"></span>"."</a>";
    			$codeBloc .= "</li>";
    		}
    		$codeBloc .= "</ul>";
    		return $codeBloc;
    	}
    	else
    	{
    		return;
    	}
    }




    function reCheck($pattern, $value)
    {
    	if (preg_match($pattern, $value, $matches)) 
        {
          return $matches[0];
        }
        else 
        {
          return "invalid_data";  
        }
    }




    function checkboxCheck($value)
    {
        if ($value)
        {
          return "true";
        }
        else 
        {
          return "invalid_data";  
        }
    }




    function validateValue($value, $flag)
    {
    	switch ($flag) {
    	    case "EMAIL":
        		$pattern = '/^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,})$/';
    	        break;
    	    case "PHONE":
        		$pattern = '/^((([0-9]{1})*[- .(]*([0-9]{3})[- .)]*[0-9]{3}[- .]*[0-9]{4})+)*$/';
    	        break;
    	    case "ALPHA":
        		$pattern = '/^[[:space:]\p{L}\p{Pd}()]*$/u';
    	        break;
    	    case "ALPHANUMERIC":
        		$pattern = '/^[[:space:]\p{L}\p{Pd}\p{P}()0-9!]*$/u';
    	        break;
    	    case "BOX":
    	    	return checkboxCheck($value);
    	        break;
    	}
    	return reCheck($pattern, $value);
    }




    function sanitizeForm($_postArray)
    {
    	$sanitizedForm = array();
    	foreach ($_postArray as $key => $value)
        {
    		$xxx = strpos($key, 'xxx_');
    		if ($xxx === false) {
        		$sanitizedForm[$key] = isset( $value ) 
    	           					   ? strip_tags( nl2br(trim( $value )), "<br>" ) 
    	           					   : NULL;
    		}
    	}
    	return $sanitizedForm;
    }


    function validateRegistration($_postArray)
    {
        $sanitizedValues = sanitizeForm($_postArray);
        $_required = [
            "email"
                => validateValue( $sanitizedValues['fld_register_email'], "EMAIL" ),
            "email_confirm"
                => validateValue( $sanitizedValues['fld_register_email_confirm'], "EMAIL" ),
            "first_name"
                => validateValue( $sanitizedValues['fld_register_fn'], "ALPHA" ),
            "last_name"
                => validateValue( $sanitizedValues['fld_register_ln'], "ALPHA" ),
            "password"
                => validateValue( $sanitizedValues['fld_register_psw'], "ALPHANUMERIC" ),
            "password_confirm"
                => validateValue( $sanitizedValues['fld_register_psw_confirm'], "ALPHANUMERIC" ),
            "captcha" 
                => $sanitizedValues['g-recaptcha-response']
        ];
        
        $ajaxMsg  = "<h4>Submission Error</h4><p>Please review the following field(s):</p>";
        $ajaxMsg .= "<ul>";
        
        $requiredCheck = 0;
        foreach ($_required as $key => $value) {
            // echo "-- Key: $key; Value: $value<br />\n";
            if (!$value)
            {
                $requiredCheck++;
                switch ($key) {
                    case "email":
                        $ajaxMsg .= "<li>The <a href=\"#f-register-email\">".htmlspecialchars($key)."</a> is required.</li>";
                        break;
                    case "email_confirm":
                        $ajaxMsg .= "<li>The <a href=\"#f-register-email-confirm\">".htmlspecialchars($key)."</a> is required.</li>";
                        break;
                    case "first_name":
                        $ajaxMsg .= "<li>The <a href=\"#f-register-fn\">".htmlspecialchars($key)."</a> is required.</li>";
                        break;
                    case "last_name":
                        $ajaxMsg .= "<li>The <a href=\"#f-register-ln\">".htmlspecialchars($key)."</a> is required.</li>";
                        break;
                    case "password":
                        $ajaxMsg .= "<li>The <a href=\"#f-register-password-confirm\">".htmlspecialchars($key)."</a> is required.</li>";
                        break;
                    case "password_confirm":
                        $ajaxMsg .= "<li>The <a href=\"#f-register-password\">".htmlspecialchars($key)."</a> is required.</li>";
                        break;
                    case "captcha":
                        $ajaxMsg .= "<li>The <a href=\"#captcha\">".htmlspecialchars($key)."</a> checkbox is required.</li>";
                        break;
                }
            }
            else if ($value == "invalid_data")
            {
                $requiredCheck++;
                switch ($key) {
                    case "email":
                        $ajaxMsg .= "<li>The <a href=\"#f-register-email\">".htmlspecialchars($key)."</a> is not valid.</li>";
                        break;
                    case "email_confirm":
                        $ajaxMsg .= "<li>The <a href=\"#f-register-email-confirm\">".htmlspecialchars($key)."</a> is not valid.</li>";
                        break;
                    case "first_name":
                        $ajaxMsg .= "<li>The <a href=\"#f-register-fn\">".htmlspecialchars($key)."</a> is not valid.</li>";
                        break;
                    case "last_name":
                        $ajaxMsg .= "<li>The <a href=\"#f-register-ln\">".htmlspecialchars($key)."</a> is not valid.</li>";
                        break;
                    case "password":
                        $ajaxMsg .= "<li>The <a href=\"#f-register-password\">".htmlspecialchars($key)."</a> is not valid.</li>";
                        break;
                    case "password_confirm":
                        $ajaxMsg .= "<li>The <a href=\"#f-register-password-confirm\">".htmlspecialchars($key)."</a> is not valid.</li>";
                        break;
                }
            }
        }

        if ($_required['email'] != $_required['email_confirm'])
        {
            $requiredCheck++;
            $ajaxMsg .= "<li>The <a href=\"#f-register-email\">Email</a> confirmation missmatch.</li>";
        }
        else if ($_required['password'] != $_required['password_confirm'])
        {
            $requiredCheck++;
            $ajaxMsg .= "<li>The <a href=\"#f-register-password\">Password</a> confirmation missmatch.</li>";
        }
        
        $ajaxMsg .= "</ul>";

        if ($requiredCheck != 0)
        {
            echo($ajaxMsg);

            // DEBUG
            // labelvalueSplit($_required);
            // debug_SubmitTable(labelvalueSplit($_required));
            // debug_rawTable($_required);
            exit;
        }

        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfS5ggTAAAAAKR6w3mDTrT9i7edXNxnmhBl4Kl9&response=" . $_required['captcha'] . "&remoteip=" . $_SERVER['REMOTE_ADDR']);

        if($response == false)
        {
            echo('<h4>Unable to proceed with your request.</h4>');
            exit;
        }

        return $_required;
    }


    function validateForm($_postArray)
    {
        $sanitizedValues = sanitizeForm($_postArray);
        $_required = [ 
            "Project Name"
                => validateValue( $sanitizedValues['fld_project_name'], "ALPHANUMERIC" ),
            "Primary Contact First Name"
                => validateValue( $sanitizedValues['fld_contact_primary_fn'], "ALPHA" ),
            "Primary Contact Last Name"
                => validateValue( $sanitizedValues['fld_contact_primary_ln'], "ALPHA" ),
            "Primary Contact Phone Number"
                => validateValue( $sanitizedValues['tel_contact_primary_tel'], "PHONE" ),
            "Primary Contact Email" 
                => validateValue( $sanitizedValues['eml_contact_primary_email'], "EMAIL" ),
            "Primary Contact Email Verification"
                => validateValue( $sanitizedValues['eml_contact_primary_email_verification'], "EMAIL" ),
            // "Terms & Condition"
            //     => validateValue( $sanitizedValues['bol_t_and_c_reviewed'], "BOX" ),
            "captcha" 
                => $sanitizedValues['g-recaptcha-response']
        ];
        
        $ajaxMsg  = "<h4>Submission Error</h4><p>Please review the following field(s):</p>";
        $ajaxMsg .= "<ul>";
        
        $requiredCheck = 0;
        foreach ($_required as $key => $value) {
            if (!$value)
            {
                $requiredCheck++;
                switch ($key) {
                    case "Project Name":
                        $ajaxMsg .= "<li>The <a href=\"#f-project-name\">".htmlspecialchars($key)."</a> is required.</li>";
                        break;
                    case "Primary Contact First Name":
                        $ajaxMsg .= "<li>The <a href=\"#f-contact-firstname-1\">".htmlspecialchars($key)."</a> is required.</li>";
                        break;
                    case "Primary Contact Last Name":
                        $ajaxMsg .= "<li>The <a href=\"#f-contact-lastname-1\">".htmlspecialchars($key)."</a> is required.</li>";
                        break;
                    case "Primary Contact Phone Number":
                        $ajaxMsg .= "<li>The <a href=\"#f-contact-phone-1\">".htmlspecialchars($key)."</a> is required.</li>";
                        break;
                    case "Primary Contact Email":
                        $ajaxMsg .= "<li>The <a href=\"#f-contact-email-verification-1\">".htmlspecialchars($key)."</a> is required.</li>";
                        break;
                    case "Primary Contact Email Verification":
                        $ajaxMsg .= "<li>The <a href=\"#f-contact-email-validator-1\">".htmlspecialchars($key)."</a> is required.</li>";
                        break;
                    // case "Terms & Condition":
                    //     $ajaxMsg .= "<li>The <a href=\"#f-condition\">".htmlspecialchars($key)."</a> checkbox is required.</li>";
                    //     break;
                    case "captcha":
                        $ajaxMsg .= "<li>The <a href=\"#captcha\">".htmlspecialchars($key)."</a> checkbox is required.</li>";
                        break;
                }
            }
            else if ($value == "invalid_data")
            {
                $requiredCheck++;
                switch ($key) {
                    case "Project Name":
                        $ajaxMsg .= "<li>The <a href=\"#f-project-name\">".htmlspecialchars($key)."</a> is not valid.</li>";
                        break;
                    case "Primary Contact First Name":
                        $ajaxMsg .= "<li>The <a href=\"#f-contact-firstname-1\">".htmlspecialchars($key)."</a> is not valid.</li>";
                        break;
                    case "Primary Contact Last Name":
                        $ajaxMsg .= "<li>The <a href=\"#f-contact-lastname-1\">".htmlspecialchars($key)."</a> is not valid.</li>";
                        break;
                    case "Primary Contact Phone Number":
                        $ajaxMsg .= "<li>The <a href=\"#f-contact-phone-1\">".htmlspecialchars($key)."</a> is not valid.</li>";
                        break;
                    case "Primary Contact Email":
                        $ajaxMsg .= "<li>The <a href=\"#f-contact-email-verification-1\">".htmlspecialchars($key)."</a> is not valid.</li>";
                        break;
                    case "Primary Contact Email Verification":
                        $ajaxMsg .= "<li>The <a href=\"#f-contact-email-validator-1\">".htmlspecialchars($key)."</a> is not valid.</li>";
                        break;
                    // case "Terms & Condition":
                    //     $ajaxMsg .= "<li>The <a href=\"#f-condition\">".htmlspecialchars($key)."</a> checkbox is not properly checked.</li>";
                    //     break;
                }
            }
        }
        
        $ajaxMsg .= "</ul>";

        if ($requiredCheck != 0) {

            echo($ajaxMsg);

            // DEBUG
            labelvalueSplit($sanitizedValues);
            debug_SubmitTable(labelvalueSplit($sanitizedValues));
            debug_rawTable($sanitizedValues);
            exit;
        }

        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfS5ggTAAAAAKR6w3mDTrT9i7edXNxnmhBl4Kl9&response=".$_required['captcha']."&remoteip=".$_SERVER['REMOTE_ADDR']);
        if($response==false)
        {
            echo('<h4>Unable to proceed with your request.</h4>');
            exit;
        }
        else
        {
            // submitEmail($sanitizedValues);
            return $sanitizedValues;
        }
    }




    // Function use in labelvalueSplit(); -> Process and extract the "hours of operation" data in a single string
    function formatHours($hours_set_name, $posts, $option)
    {
        $hours_setID;
        $re_setID = '/\B[1-7]{1}\B/';
        $isLabel = $option == "LAB";
        $return_string;

        if (preg_match($re_setID, $hours_set_name, $matches)) 
        {
            $hours_setID = $matches[0];

            // If hours range value is set
            if ( $posts['opt_hours_regular_set_'. $hours_setID .'_range'] !== "n/a" )
            {
                // If the CLOSED checkbox is checked
                if ( isset( $posts['cbx_hours_regular_set_'. $hours_setID .'_closed'] ) )
                {
                    // If this iteration is to set the LABEL value
                    if ($isLabel)
                    {   
                        $return_string = $posts['opt_hours_regular_set_'. $hours_setID .'_range'];
                    }
                    // If this iteration is to set the VALUE data
                    else
                    {
                        $return_string = sprintf(  _( 'fld_hours_closed' ) );
                    }
                }

                // If the CLOSED checkbox is NOT checked
                else
                {
                    $identifier = [ "_range", "_start_h", "_start_m", "_end_h", "_end_m" ];
                    for ($i = 0, $j = count($identifier); $i < $j ; $i++) {

                        $id[$identifier[$i]] = isset( $posts['opt_hours_regular_set_'. $hours_setID . $identifier[$i] ] )
                                               ? $posts['opt_hours_regular_set_'. $hours_setID . $identifier[$i] ]
                                               : "_debug: No Range Selected";
                    }

                    // If this iteration is to set the LABEL value
                    if ($isLabel) 
                    {
                        $return_string  = $id["_range"];
                    }
                    // If this iteration is to set the VALUE data
                    else
                    {
                        $return_string  = sprintf( _( 'fld_hours_opened' ) );
                        $return_string .= " ";
                        $return_string .= sprintf( _( 'fld_hours_from' ) );
                        $return_string .= " ";
                        $return_string .= $id["_start_h"];
                        $return_string .= $id["_start_m"];
                        $return_string .= " ";
                        $return_string .= sprintf( _( 'fld_hours_to' ) );
                        $return_string .= " ";
                        $return_string .= $id["_end_h"];
                        $return_string .= $id["_end_m"];
                    }

                }

            }

            // If hours range value is NOT set
            else
            {
                // no range slected
                $return_string = sprintf( _( '_debug-hours-norange' ) );
            }

            return $return_string;
        }
        else 
        {
          return sprintf( _( '_debug-hours-invalidrange' ) );  
        }
    }

    // Split Label / Values in key/value pair array
    function labelvalueSplit($_postArray)
    {
        $_posts = $_postArray;
        $_postsLabel = [];
        $_postsValue = [];

        // Replace NULL values with "n/a"
        foreach ($_posts as $key => $value)
        {
            if (!$value)
            {
                $_posts[$key] = "n/a";
            }
        }

        // Process submission post for output in email layout
        foreach ($_posts as $key => $value)
        {
            // Exclude procedural key/pairs values not required in email/file output
            $isOutputField = $key != "eml_contact_primary_email_verification" &&
                             $key != "eml_contact_alt_email_verification" &&
                             $key != "eml_billing_email_verification" &&
                             $key != "bol_t_and_c_reviewed" &&
                             $key != "g-recaptcha-response" &&
                             $key != "submit" &&
                             $value != "n/a" &&
                             $value != NULL;

            if ($isOutputField)
            {
                // "Hours of operation" data require separate handling bcause of the multiple input fields for hours and minutes selection.
                if ( preg_match('/^opt_hours_regular_set_\d_range$/', $key, $match) )
                {
                    $rangeName = substr($match[0], 0, 23);

                    // RANGE
                    $_postsLabel[$match[0]] = _($match[0]);
                    $_postsValue[$match[0]] = formatHours($key, $_posts, "LAB");

                    // HOURS
                    $id = $rangeName."_hours";
                    $_postsLabel[$id] = _($id);
                    $_postsValue[$id] = formatHours($key, $_posts, "VAL");
                }

                // Handling the remaining post items in two arays to facilitate L18N integration.
                else if ( !preg_match('/^opt_hours_regular_set_\d_(start|end)_(h|m)$/', $key, $match) && !preg_match('/^cbx_hours_regular_set_\d_closed$/', $key, $match) ) 
                {
                    $_postsLabel[$key] = _($key);
                    $_postsValue[$key] = $value;
                }
            }
        }

        $returned_array = array(

            "label" => $_postsLabel,
            "value" => $_postsValue

        );
        return $returned_array;
    }




    function submitEmail($_postArray) {

        $_post = labelvalueSplit($_postArray);

        // Get the email template and store it in a variable
        ob_start();
        require(__DIR__ . "/../mail/email_html.php");
        $email_html = ob_get_clean();

        // Get the plain email template and store it in a variable
        ob_start();
        require(__DIR__ . "/../mail/email_plain.php");
        $email_plain = ob_get_clean();

        // Get the plain email template and store it in a variable
        ob_start();
        echo debug_SubmitTable($_post);
        $email_htmltable = ob_get_clean();

        $attach_dir = __DIR__."/../mail/attach";
        $attach_name = _( 'process_email_attach_name' );
        $file_plain = $attach_dir . "/" . $attach_name . ".txt";
        $file_htmltable = $attach_dir . "/" . $attach_name . ".html";

        if( chmod($attach_dir, 0755) )
        {
            chmod($attach_dir, 0777);

            // Write the contents back to the file
            file_put_contents($file_plain, $email_plain, LOCK_EX);
            file_put_contents($file_htmltable, $email_htmltable, LOCK_EX);

            if( chmod($attach_dir, 0777) ) {
                chmod($attach_dir, 0755);
            }
        }

        $plain_attachment = chunk_split(base64_encode(file_get_contents($file_plain)));
        $htmltable_attachment = chunk_split(base64_encode(file_get_contents($file_htmltable)));
        
        $email_subject = "New Form Submission from " . $_post['value']['fld_contact_primary_fn'] . " " . $_post['value']['fld_contact_primary_ln'] . " | " . $_post['value']['fld_project_name'];
        
        // boundarie
        $semi_rand = md5(time()); 
        $mime_boundary = "BOUNDARY_mixed_{$semi_rand}"; 
        $alt_mime_boundary = "BOUNDARY_alt_{$semi_rand}"; 

        $email_headers  = "From: " . "do_not_reply@danwebco.ca" . "\r\n";
        $email_headers .= "Reply-To: " . $_post['value']['eml_contact_primary_email'] . "\r\n";
        // $email_headers .= "Cc: " . $_post['value']['eml_contact_primary_email'] . "\r\n";
        $email_headers .= "Content-Type: multipart/mixed; boundary=\"{$mime_boundary}\"\r\n";
        // $email_headers .= "MIME-Version: 1.0\r\n"; // if I add this header, gmail tag it as spam... no clue how to fix this

        $email_message = "\r\n--{$mime_boundary}\r\n";
        $email_message .= "Content-Type: multipart/alternative; boundary=\"{$alt_mime_boundary}\"\r\n";
        
        $email_message .= "\r\n--{$alt_mime_boundary}\r\n";
        $email_message .= "Content-Type: text/plain; charset=UTF-8; format=\"fixed\"\r\n".
                          // "Content-Transfer-Encoding: 7bit\r\n".
                          "Content-Transfer-Encoding: quoted-printable\r\n".
                          "Content-Disposition: inline\r\n".
                          $email_plain;

        $email_message .= "\r\n--{$alt_mime_boundary}\r\n";
        $email_message .= "Content-Type: text/html; charset=UTF-8\r\n".
                          // "Content-Transfer-Encoding: 7bit\r\n".
                          "Content-Transfer-Encoding: quoted-printable\r\n".
                          "Content-Disposition: inline\r\n".
                          $email_html;
        $email_message .= "\r\n--{$alt_mime_boundary}--\r\n";
        
        $email_message .= "\r\n--{$mime_boundary}\r\n";
        $email_message .= "Content-Type: text/plain; charset=UTF-8; name=\"" . $attach_name . ".txt\"\r\n".
                          "Content-Disposition: attachment; filename=\"" . $attach_name . ".txt\"\r\n".
                          "Content-Transfer-Encoding: base64\r\n".
                          "\r\n".
                          $plain_attachment;

        $email_message .= "\r\n--{$mime_boundary}\r\n";
        $email_message .= "Content-Type: text/html; charset=UTF-8; name=\"" . $attach_name . ".html\"\r\n".
                          "Content-Disposition: attachment; filename=\"" . $attach_name . ".html\"\r\n".
                          "Content-Transfer-Encoding: base64\r\n".
                          "\r\n".
                          $htmltable_attachment;
        
        $email_message .= "\r\n--{$mime_boundary}--\r\n";

        //send the email
        $mail = mail( $_post['value']['eml_contact_primary_email'], $email_subject , $email_message, $email_headers );


        if( chmod($attach_dir, 0755) )
        {
            chmod($attach_dir, 0777);
            unlink($file_plain);
            unlink($file_htmltable);

            if( chmod($attach_dir, 0777) )
            {
                chmod($attach_dir, 0755);
            }
        }

        // DEBUG Ouput the result of sending the email in the cron notification email
        echo $mail ? "\n\nMail sent\n\n" : "\n\nMail failed\n\n";

    }




    function setTheme() {

        if (isset($_COOKIE['theme']))
        {
            setcookie('theme', $_COOKIE['theme'], time() + (3600 * 24 * 30));
            return $_COOKIE['theme'];
        }
        else
        {
            $theme = "th-light";
            setcookie('theme', $theme, time() + (3600 * 24 * 30));
            return $theme;
        }
    }