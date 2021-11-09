<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Edit user details</title>
        <link href="../css/lofi.css" type="text/css" rel="stylesheet" media="all"/>
    </head>
    <body><div>
        <h1><?php htmlout($pagetitle); ?></h1>

<!--<form action="http://www.poloafrica.com/cgi-bin/nmsfmpa.pl" id="contactform" method="post" name="contactform">-->
<!-- The form is the ONLY 'article' that remains HARDCODED-->
<div><form action="" id="poloafricacontactform" method="post" class="<?php echo $state; ?>">
		<fieldset>
            <legend><?php echo $fieldset; ?></legend><label title="required field" for="name" <?php $echo('name');?>>name</label><input id="name" name="name" tabindex="1" value="<?php $echo('name', true);?>" pattern="\S+\s\S{2,}" required=""><label for="phone" <?php $echo('phone');?>>phone</label><input id="phone" name="phone" tabindex="2" type="tel" value="<?php $echo('phone', true);?>" pattern="\d\s?{7,}"><label for="email" <?php $echo('email');?> title="required field">email</label><input id="email" name="email" tabindex="3" type="email" value="<?php $echo('email', true); ?>"><label for="addr1">address</label><input id="addr1" name="addr1" tabindex="4" value="<?php $echo('addr1');?>"><label for="addr2">address</label><input id="addr2" name="addr2" tabindex="5" value="<?php $echo('addr2');?>"><label for="addr3">address</label><input id="addr3" name="addr3" tabindex="6" value="<?php $echo('addr3');?>"><label for="addr4">address</label><input id="addr4" name="addr4" tabindex="7" value="<?php $echo('addr4');?>"><label for="country">country</label><input id="country" name="country" tabindex="8" value="<?php $echo('country');?>"><label for="postcode">postcode</label><input id="postcode" name="postcode" tabindex="9" value="<?php $echo('postcode');?>">
            <p id="web"><label for="url"><input id="url" name="url"></label></p>
		</fieldset>
		<fieldset>
            <textarea id="comments" name="comments" tabindex="9"><?php
                //$item variable is used in fieldset/legend set to empty string if form has yet to be submitted
                if(empty($item)){
                    echo trim($text);
                }
                   else {
                       //sticky: if form submission fails on a required field
                       $echo('comments', true);
                   }
                ?></textarea><input alt="" id="dogs" name="dogs" src="../images/resource/dogsform.png" tabindex="10" type="image"><input type="submit" value="submit">
		</fieldset>
    </form>
    <?php if($isPriv()) { ?>
        <p><a href="../clients/">Edit Clients</a></p>
        <?php } ?>
        <p><a href="<?php echo $data['ret']; ?>">Return to <?php echo $data['page']; ?></a></p>
        </div></body>
</html>