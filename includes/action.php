<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/poloafrica/classes/ArticleFactory.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Checker.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Utility.php';

function buildMessage($k, $v, $flag)
{
    $ret = $flag ? "" : "\r\n\r\n";//!MUST BE DOUBLE QUOTES!
    $str = ucfirst($k) . ': ' . $v;
    return $str . $ret;
}

function stringMin($v) {
    return strlen($v) > 15;
}

function stringMax($v) {
    return strlen($v) < 1000;
}

$host = 'north.wolds@btinternet.com';
//$host = 'info@poloafrica.com';
$to = 'andrewsykes@btinternet.com';
$expected = array(
    'name',
    'email',
    'msg',
    'phone',
    'addr1',
    'addr2',
    'addr3',
    'addr4',
    'postcode',
    'country',
    'comments'
);
//"<h3><a href='#' id='contact_form'>Poloafrica contact form</a></h3>";
$form_start = $doConcat('<h3><a href="#" id="contact_form">');
$form_warning = $doConcat('<h3 class="warning"><a href="#" id="contact_form">');
$form_success = $doConcat('<h3 class="complete"><a href="#" id="contact_form">');
$form_txt = $doConcat('Poloafrica contact form');
$form_success_txt = $doConcat('Poloafrica contacted!');
$form_end = $doConcat('</a></h3>');
$textarea = $compose($doConcat('<textarea id="comments" name="comments" tabindex="9"'), 'doEcho');
$doTextAreaEnd = $doConcat('</textarea>');
//reesult of first function gets passed to next function
$doTextArea = $compose($doConcat('>'), $textarea);
$doWarningArea = $compose($doConcat(' class="warning">'), $textarea);
$text = "Use this area for comments or questions";
$post_text = 'Please enter your message';
$state = '';
$fieldset = 'Poloafrica contact form';
$item = 'item';
$heading = 'Poloafrica contact form';
$echo = function ()
{
};
$suspect = false;
$suspect_pattern = '/to:|cc:|bcc:|content-type:|mime-version:|multipart-mixed:|content-transfer-encoding:/i';
$pairs = array(
    'phone' => 'email'
);

$empty = new Checker('this is a required field', new Negator(new Empti()));
$subtext = substr($text, 0, 13);
$subpost_text = substr($post_text, 0, 13);
$isNum = new Checker('please supply a phone number', new PhoneNumber());
$isEmail = new Checker('please supply an email address', new isEmail());
$isName = new Checker('please supply name in the expected format: "FirstName Middle/LastName LastName"', new isName());
$isSmallMsg = new Checker('Message is very small, please elaborate', new isSmallMsg());
$isLargeMsg = new Checker('Word count of your message is too great. Reduce word count or please email instead', new isLargeMsg());
$comment = new Checker($post_text, new Negator(new Match("/^$subtext/")));
$postcomment = new Checker($post_text, new Negator(new Match("/^$subpost_text/")));
$required = array(
    'name' => preconditions($empty, $isName) ,
    'email' => preconditions($empty, $isEmail) ,
    'comments' => preconditions($empty, $comment, $postcomment, $isSmallMsg, $isLargeMsg)
);
?>
<article id="contactarea" class="alt">
    <?php
    if (!empty($_POST))
{
    $message = '';
    $missing = array();
         
    //genuine values trimmed, suspect values ('To:') /etc replaced with a single space /^\s$/
    //input type of image button returning x and y values in form submission BUT NOT WITH AJAX
    //$data = array_map('spam_scrubber', array_slice($_POST, 0, count($_POST) - 2));
    $data = array_map('spam_scrubber', $_POST);
    $suspect = !empty(array_filter($data, 'single_space'));
    //honeypot
    if (!$suspect && $_POST['url'])
    {
        $suspect = true;
    }
    if (!$suspect)
    {
        foreach ($data as $k => $v)
        {
            if (isset($required[$k]))
            {
                $res = $required[$k]('identity', $v);
                //$res will be a string if valid, or an array of issues
                if (is_array($res))
                {
                    $missing[$k] = $res;
                    $k = null;
                }
            }
            if (in_array($k, $expected))
            {
                //sets vars used below, $email, $comments
                ${$k} = trim($v);
                $message .= buildMessage($k, $v, $k === 'comments');
            }
        } //each
        
    }
    if (empty($missing))
    {
        $message = wordwrap($message, 70);
        $headers = "From: $host";
        $headers .= "\r\nContent-Type: text/plain; charset=utf-8";
        $headers .= "\r\nReply-To: $email";
        $mailsent = mail($host, 'Website Enquiry', $message, $headers);
        $heading = 'contacted';
        //$mailsent = true;
        
        if ($mailsent)
        {
            unset($missing);
            /*EARLIER VERSIONS OF PHP REQUIRE FIRST ASSIGNING CLOSURE TO A VARIABLE
            THIS THROWS A SYNTAX ERROR UNEXPECTED '(' : $cb = $compose($form_success_txt, $form_success, 'doEcho')('</a></h3>');*/
            $cb = $compose($form_success_txt, $form_success, 'doEcho');
            $cb('</a></h3>');
    ?>
            <div id="response">
                <figure class="dogs bottom"><img alt="" src="../images/resource/dog_gone.jpg"></figure>
                <figure class="dogs top"><img alt="" src="../images/resource/016.jpg" ></figure>
                <div><h1>Thankyou for your enquiry</h1>
                    <p>An email has been sent to <a href="mailto:<?php htmlout($email); ?>"><em><?php htmlout($email); ?></em></a></p>
                    <p><em>Here is your message</em>:</p>
                    <p class="msg"><?php htmlout($comments); ?></p>
                </div>
                <figure class="bottom cat"><img alt="cat" src="../images/resource/cat_real_gone.jpg" ></figure>
                <figure class="top cat"><img alt="cat" src="../images/resource/cat_gone.jpg"></figure>
            </div>
            <?php
        } //sent
        else
        { ?>
<div id="response" class="warning">
    <h1>Sorry, There was a problem sending your message. Please try again later.</h1></div>
            <?php
        } //not sent
        
    } //ok
    else
    {
        $item = count($missing) > 1 ? 'items' : 'item';
        $fieldset = "Please complete the missing $item indicated";
        $state = 'warning';
        $echo = $compose(flushMsgCb($missing, $data), 'doEcho');
        $partial_echo = flushMsgCb($missing, $data);
        //https://stackoverflow.com/questions/24403817/html5-required-attribute-one-of-two-fields
        $cb = $compose($form_txt, $form_warning, 'doEcho');
        $cb('</a></h3>');
        include 'form.html.php'; //new
    }
} //posted
else if (!isset($missing))
{ //not yet posted
    //$compose($form_txt, $form_start, 'doEcho')('</a></h3>');
    $cb = $compose($form_txt, $form_start, 'doEcho');
    $cb('</a></h3>');
    //echo "<h3><a href='#' id='contact_form'>Poloafrica contact form</a></h3>";
    $item = null; //used as a flag to supply default text to textarea
    include 'form.html.php'; //new
}