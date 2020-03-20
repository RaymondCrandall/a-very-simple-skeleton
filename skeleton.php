<?

/*

A Very Simple Skeleton
for information management

~here is your superfast way to build something~


If anyone knows how to build command line generators
I would highly recommend forking this project



this will hopefully make it really easy to start building out a simple
way to work with information. no frameworks, no fancy stuff
some people won't know, just php using mysql
the data sanitization steps will need to be added,
but regular expressions and data sanitization is based on the DATABASE
to make use of this you need to establish a data model

there are much better ways to do this but sometimes you need to go fast

*/



# if you want to debug and have no admin session handling
# integrated with debugging tools such as Kint ...
# require_once 'Kint.class.php';

################################################################
#
#	HTTP REQUEST HANDLING, VALIDATION, FORMATTING LOGIC
#
################################################################

#examplesbeen interacted with? $_GET has priority over $_POST

$example_id = (!empty($_GET[exampleid])?$_GET[exampleid]:(!empty($_POST[exampleid])?$_POST[exampleid]:''));

$width = (!empty($_GET[width])?$_GET[width]:(!empty($_POST[width])?$_POST[width]:''));

$height = (!empty($_GET[height])?$_GET[height]:(!empty($_POST[height])?$_POST[height]:''));

$action = (!empty($_GET[action])?$_GET[action]:(!empty($_POST[action])?$_POST[action]:''));

################################################################
#
#	DATABASE [ PHP , MySQLi ] FUNCTIONS BELOW
#
################################################################


# return MySQLi OBJECT ; requires MySQL query STRING;
function query( $request )
{
	$db = new mysqli('server_name','mysql_user_name','password','table_name');

	if ( $response = $db->query( $request ) )
	{
		$db->close();
		return $response;
	}

}


function process_example( $example_id , $width , $height )
{
	if ( !empty($example_id) || !empty($width) || !empty($height) ){
		$request =	 'INSERT INTO `example_table` '
					.'(`id`,`example_id`,`x`,`y`) '
				.'VALUES ( '
					.'NULL , '
					.'\''.	strtolower(	$example_id ) 	.'\' , '
					. 			$width  	.' , '
					. 			$height		.' '
				.')';
		$response = query( $request );

		return $response;
	} else {
		return NULL;
	}
}


function check_match( $table , $column , $row_id )
{
	$response = query( 'SELECT * FROM `'.$table.'` WHERE `'.$column.'` = \''.$row_id.'\'' );
	return $response->num_rows;
}



function find_example( $example_id ){
	$matches = check_match( 'example_table' , 'example_id' , $example_id );
	$message = '';
	if( $matches  )
	$message = $matches .' matches found';
	define( MATCHES_FOUND , $message);
	unset($message);
}



function list_examples_in_table_rows(){
	# Make sure output array is declared and empty each time function is called
	$output = array();

	$example_result = 	query( 	'SELECT * FROM `example_table` '.
				'ORDER BY `example_id`'.
				'ASC'
			);
	define(RESULT_COUNT ,  $example_result->num_rows);
	# Loop through MySQLi response-OBJECT's child-OBJECT instances
	while( $example = mysqli_fetch_object( $example_result ) )
	{
		# This seemed like the craziest looking, most expedient, and simple way.
		# I am taking the Object data and making
		# a multi-dimensional array of strings with it

		# array_push is fastest way to build arrays in PHP
		# Citation:[ http://us2.php.net/manual/en/function.array-push.php#84959 ]
		array_push( $output ,

		# for those of you who do not know the conventions of the database, this could help you

			# beginning of the row
			"\n\t", '<tr id=\'',	$example->example_id,'\'>',

			#data cell
			"\n\t\t",	'<td>', $example->example_id,'</td>', # <th>example id</th>


			#data cell
			"\n\t\t",	'<td>', $example->x	,'</td>', # <th>x</th>


			#data cell
			"\n\t\t",	'<td>', $example->y	,'</td>', # <th>y</th>

			# end of the row
			"\n\t",	'</tr>'

		);# end of mysqli result object child instance AND generated array iteration
	}# end of MySQLi $example result loop
	unset( $example_result, $example );
	return $output;
}



# FUNCTION: RENDER OUTPUT
# concatenates the chunks of data the above function generates
# change this and you won't be able to generate the results
function render_output( $data )
{
	$output = '';
	foreach( $data as $key => $value )
	{
		switch( is_array( $value ) )
		{
			case true:
				$output .= implode( $value );
			break;

			case false:
			default:
				$output .= $value ;
			break;
		}
	}
	unset ( $data );
	define($result_count,count($data));
	return $output;
}




# FUNCTION: BUILD EXAMPLE
# grab the example information, render the table columns, and assign it to a keyword
function list_examples(  )
{
	$example_tr = list_examples_in_table_rows( );
	define( EXAMPLE_LIST , render_output( $example_tr ) );
	# then free up the memory occupied by the raw data
	unset( $example_tr );
}


function format_example_id_s()
{	$example_query = 'SELECT `id` AS `result_instance` , `example_id` AS `id` FROM `example_table`';
	#echo $example_query;
	$request = query( $example_query );

	while( $example = mysqli_fetch_object( $request ) )
	{
		$update=	 'UPDATE `example_table` '
		    		 .'SET `example_id` = \''. strtolower( $example->id  ) .'\' '
		    		 .'WHERE `id` = \''. $example->result_instance .'\'';
		#echo '<p>'.$update.'</p>';
		query( $update );
	}
}


###############################################################
#
# 	PROCESS REQUESTED ACTIONS BELOW
#
###############################################################

switch($action)
{
	case 'format_example_id':
		format_example_id_s();
		list_examples();
	break;

	case 'scan_example':

		find_example($example_id);
		list_examples();
	break;



	case 'process_example':

		process_example( $example_id , $width , $height );
		list_examples();
	break;



	case 'list':
	default:

		 list_examples( );
	break;
}

###############################################################
#
#	HTTP RESPONSE HEADER AND BODY CONTENT BELOW
#
###############################################################
?>
<!doctype html>
<html lang=en-us>
<head>
<meta charset=utf-8>
	<title>Example Data Entry</title>
	<link
		rel='stylesheet'
		type='text/css'
		href='./../../general.css'
		media='screen' />


</head>
<body>
<h1>Manage Examples</h1>
<div class='right'>
	<h2>Scan Example</h2>
	<div>
		<form method='post' action='./'>
		<p><?= MATCHES_FOUND ?></p>
		<p><input type='hidden' id='action' name='action' value='scan_example' />
		<p>
		<input class='right' type='submit' name='submit' value='finish' />
		<label for='barcode'>Scan Barcode</label>
			<input type='text' id='exampleid' name='exampleid' value='' autofocus />
		</p>
	</form>
	</div>

	<?
	# SECTION: EXAMPLE RECORDS
	# PURPOSE: List EXAMPLE INFORMATION
	?>
	<div class='left'>
	<h2>Example Records</h2>
	<?
	if( RESULT_COUNT > 0 ){
	?>
	<table>
	<tr>
		<th>Example Code</th>
		<th>width</th>
		<th>height</th>
	</tr>
	<?= EXAMPLE_LIST ?>
	</table>
	</div><? }else{
	#
	# IF NO RESULTS ARE PRESENT INSTRUCT PEOPLE TO SCAN
	?>
	<p>Next you need to scan a example.</p>
	<? # END SECTION EXAMPLE RECORDS
	} ?>

	<?
	# SECTION : ADD EXAMPLE
	# PURPOSE : add example information to database
	?>
	<div class='right'>
	<h2>Add Example <?= (!empty($example_id)?$example_id:'')?></h2>
		<form method='post' action='./'>
		<p><input type='hidden' id='action' name='action' value='process_example' />
		<label for='example-width'>width</label>
		<input type='text' id='example-width' name='width' value='' /></p>
		<p><label for='example-height'>height</label>
		<input type='text' id='example-height' name='height' value='' /></p>
		<?php
		if(empty($example_id)){
		?>
		<p><label for='barcode'>Barcode</label>
			<input type='text' id='exampleid' name='exampleid' value='' /></p>
		<? }else{
		?>
			<input type='hidden' id='exampleid' name='exampleid' value='<?= (!empty($example_id)?$example_id:'') ?>' />
		<? } ?><input type='submit' name='submit' value='finish' />

	</form>
	</div>
	<? #}
	# END SECTION ADD EXAMPLE ?>
</body>
</html>
