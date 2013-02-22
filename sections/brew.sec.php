<?php 
/**
 * Module:      brew.sec.php
 * Description: This module houses the functionality for users to add/edit individual competition
 *              entries - references the "brewing" database table.
 *
 */
include(DB.'styles.db.php'); 
include(DB.'entries.db.php'); 

function limit_subcategory($style,$pref_num,$pref_exception_sub_num,$pref_exception_sub_array,$uid) {
	/*
	$style = Style category and subcategory number
	$pref_num = Subcategory limit number from preferences
	$pref_exception_sub_num = The entry limit of EXCEPTED subcategories
	$pref_exception_sub_array = Array of EXCEPTED subcategories
	*/
	
	$style_break = explode("-",$style);
	
	require(CONFIG.'config.php');
	mysql_select_db($database, $brewing);
	
	$pref_exception_sub_array = explode(",",$pref_exception_sub_array);
	
	$query_style = sprintf("SELECT id FROM %s WHERE brewStyleGroup='%s' AND brewStyleNum='%s'",$prefix."styles",ltrim($style_break[0],"0"),$style_break[1]); 
	$style = mysql_query($query_style, $brewing) or die(mysql_error());
	$row_style = mysql_fetch_assoc($style);
	
	// Check if the user has a entry in the system in the subcategory
	
	$query_check = sprintf("SELECT COUNT(*) as 'count' FROM %s WHERE brewBrewerID='%s' AND brewCategory='%s' AND brewSubCategory='%s'", $prefix."brewing",$uid,ltrim($style_break[0],"0"),$style_break[1]);
	$check = mysql_query($query_check, $brewing) or die(mysql_error());
	$row_check = mysql_fetch_assoc($check);
	
	if ($row_check['count'] >= $pref_num) $return = "DISABLED";
	else $return = "";
	
	if (($return == "DISABLED") && ($pref_exception_sub_array != "")) {
		if (in_array($row_style['id'],$pref_exception_sub_array)) {
			// if so, check if the amount in the DB is greater than or equal to the "excepted" limit number
			if (($row_check['count'] >= $pref_exception_sub_num)) $return = "DISABLED";
			elseif ($pref_exception_sub_num == "") $return = "";
			else $return = "";
		}
	}
	
	return $return;
	
}

function highlight_required($msg,$method) {
	
	if ($method == "0") { // special ingredients OPTIONAL mead/cider
		switch($msg) {
			case "1-24-A":
			case "1-24-B":
			case "1-24-C":
			case "1-25-A":
			case "1-25-B":
			case "1-26-B":
			case "1-26-C":
			case "1-27-B":
			case "1-27-C":
			case "1-28-C":
			return TRUE;
			break;
			
			default: 
			return FALSE;
			break;
		}
	}
	
	if ($method == "1") { // special ingredients REQUIRED beer/mead/cider
		switch($msg) {
			case "1-6-D":		
			case "1-16-E":
			case "1-17-F":
			case "1-20-A":
			case "1-21-A":
			case "1-21-B":
			case "1-22-B":
			case "1-22-C":
			case "1-23-A":
			case "1-25-C":
			case "1-26-A":
			case "1-26-C":
			case "1-27-E":
			case "1-28-A":
			case "1-28-B":
			case "1-28-D":
			case "4":
			return TRUE;
			break;
			
			default: 
			return FALSE;
			break;
		}
	}

	if ($method == "2") { // mead and cider carb/sweetness
		if (strstr($msg,"1-24")) return TRUE;
		elseif (strstr($msg,"1-25")) return TRUE;
		elseif (strstr($msg,"1-26")) return TRUE;
		elseif (strstr($msg,"1-27")) return TRUE;
		elseif (strstr($msg,"1-28")) return TRUE;
		else return FALSE;
	}
	
	if ($method == "3") { // mead strength
		if (strstr($msg,"1-24")) return TRUE;
		elseif (strstr($msg,"1-25")) return TRUE;
		elseif (strstr($msg,"1-26")) return TRUE;
		else return FALSE;
	}
	
	
	
}
?>
<?php if ($row_prefs['prefsHideRecipe'] == "N") { ?>
<script type="text/javascript" src="<?php echo $base_url; ?>/js_includes/toggle.js"></script>
<?php } ?>
<script type="text/javascript">
// Based upon http://www.9lessons.info/2010/04/live-character-count-meter-with-jquery.html
$(document).ready(function()
{
	$("#brewInfo").keyup(function()
	{
		var box=$(this).val();
		var main = box.length *100;
		var value= (main / 50);
		var count= 50 - box.length;
		
		if(box.length <= 50)
		{
		$('#count').html(count);
		}
		return false;
	}
	);
}
);
</script>
<?php 

// Show/hide special ingredients depending upon the style chosen...

$special_beer = array("6-D","16-E","17-F","20-A","21-A","21-B","22-B","22-C","23-A");
$cider = array("27-A","27-D","28-C");
$special_mead = array("24-A","24-B","24-C","25-A","25-B","25-C","26-A","26-B","26-C");
$special_cider = array("27-B","27-C","27-E","28-A","28-B","28-D");
// Get all custom cats
$query_custom_styles = sprintf("SELECT brewStyleGroup FROM %s WHERE brewStyleGroup > 28", $prefix."styles");
$custom_styles = mysql_query($query_custom_styles, $brewing) or die(mysql_error());
$row_custom_styles = mysql_fetch_assoc($custom_styles);
$totalRows_custom_styles = mysql_num_rows($custom_styles); 
if ($totalRows_custom_styles > 0) {
	do { $a[] = $row_custom_styles['brewStyleGroup']."-A"; } while ($row_custom_styles = mysql_fetch_assoc($custom_styles)); 
}
$view = ltrim($view,"1-");
?>
<script type="text/javascript">//<![CDATA[

$(document).ready(function() {
	<?php if ($action == "edit") { ?>
		$("#special").hide();
		$("#mead-cider").hide();
		$("#mead").hide();
	<?php } ?>
	<?php if (($action == "edit") && (in_array($view,$special_beer))) { ?>
		$("#special").show("slow");
		$("#mead-cider").hide();
		$("#mead").hide();
	<?php } ?>
	<?php if (($action == "edit") && (in_array($view,$cider))) { ?>
		$("#special").hide();
		$("#mead-cider").show("slow");
		$("#mead").hide();
	<?php } ?>
	<?php if (($action == "edit") && (in_array($view,$special_mead))) { ?>
		$("#special").show("slow");
		$("#mead-cider").show("slow");
		$("#mead").show("slow");
	<?php } ?>
	<?php if (($action == "edit") && (in_array($view,$special_cider))) { ?>
		$("#special").show("slow");
		$("#mead-cider").show("slow");
		$("#mead").hide();
	<?php } ?>
	<?php if (($action == "edit") && ($msg != "")) { ?>	
		<?php if (highlight_required($msg,0)) { ?>
		$("#special").show("slow");
		<?php } ?>
		<?php if (highlight_required($msg,1)) { ?>
		$("#special").show("slow");
		<?php } ?>
		<?php if (highlight_required($msg,2)) { ?>
		$("#mead-cider").show("slow");						  
		<?php } ?>
		<?php if (highlight_required($msg,3)) { ?>
		$("#mead-cider").show("slow");
		$("#mead").show("slow");
		<?php } ?>
	<?php } else { ?>
	$("#special").hide();
	$("#mead-cider").hide();
	$("#mead").hide();
	<?php } ?>
	$("#type").change(function() {
	 	$("#special").hide("fast");
		$("#mead-cider").hide("fast");;
		$("#mead").hide("fast");
		if ( 
			$("#type").val() == "25-C"){
			$("#special").hide("fast");
			$("#mead-cider").hide("fast");
			$("#mead").hide("fast");
			$("#special").show("slow");
			$("#mead-cider").show("slow");
			$("#mead").show("slow");
		}
		<?php foreach ($cider as $value) { ?>
		else if ( 
			$("#type").val() == "<?php echo $value; ?>"){
			$("#special").hide("fast");
			$("#mead").hide("fast");
			$("#mead-cider").hide("fast");
			$("#mead-cider").show("slow");
			
		}
		<?php } ?>
		
		<?php foreach ($special_mead as $value) { ?>
		else if ( 
			$("#type").val() == "<?php echo $value; ?>"){
			$("#special").hide("fast");
			$("#mead").hide("fast");
			$("#mead-cider").hide("fast");
			$("#special").show("slow");
			$("#mead").show("slow");
			$("#mead-cider").show("slow");
		}
		<?php } ?>
		
		<?php foreach ($special_cider as $value) { ?>
		else if ( 
			$("#type").val() == "<?php echo $value; ?>"){
			$("#special").hide("fast");
			$("#mead-cider").hide("fast");
			$("#mead").hide("fast");
			$("#special").show("slow");
			$("#mead-cider").show("slow");
		}
		<?php } ?>
		
		<?php foreach ($special_beer as $value) { ?>
		else if ( 
			$("#type").val() == "<?php echo $value; ?>"){
			$("#special").hide("fast");
			$("#mead-cider").hide("fast");
			$("#mead").hide("fast");
			$("#special").show("slow");
		}
		<?php } ?>
		
		<?php 
		if ($totalRows_custom_styles > 0) {
		foreach ($a as $value) { ?>
		else if ( 
			$("#type").val() == "<?php echo $value; ?>"){
			$("#mead-cider").hide("fast");
			$("#special").show("slow");
			
		}
		<?php } 
		}
		?>
		
		else{
			$("#special").hide("fast");
			$("#mead-cider").hide("fast");
			
		}	
	}
	);
}
);
</script>

<?php 

if (($action != "print") && ($msg != "default")) echo $msg_output; 
if ($row_prefs['prefsUseMods'] == "Y") include(INCLUDES.'mods_top.inc.php');
if (($action == "add") || (($action == "edit") && (($row_user['id'] == $row_log['brewBrewerID']) || ($row_user['userLevel'] <= 1)))) {
	if ($filter == "default") { ?>
<p><span class="icon"><img src="<?php echo $base_url; ?>/images/help.png"  /></span><a id="modal_window_link" href="http://help.brewcompetition.com/files/add_edit_entries.html" title="BCOE&amp;M Help: Add/Edit an Entry">Add/Edit an Entry Help</a></p>
<p>The more complete you are here, the more information will be reflected on your required entry forms (generated by clicking the printer icon from your <a href="<?php echo build_public_url("list","default","default",$sef,$base_url); ?>">list of entries</a>).</p>
<?php if (($action == "add") && ($row_prefs['prefsHideRecipe'] == "N")) { ?>
<p><span class="icon"><img src="<?php echo $base_url; ?>/images/page_code.png"  /></span>You can also <a href="<?php echo build_public_url("beerxml","default","default",$sef,$base_url); ?>">import your entry's BeerXML document</a>.</p>
<?php } } ?>
<script>
	$(function() {
		$( "#brewDate" ).datepicker({ dateFormat: 'yy-mm-dd', showOtherMonths: true, selectOtherMonths: true, changeMonth: true, changeYear: true });;
		$( "#brewBottleDate" ).datepicker({ dateFormat: 'yy-mm-dd', showOtherMonths: true, selectOtherMonths: true, changeMonth: true, changeYear: true });;
	});
	</script>
<?php 
function admin_relocate($user_level,$go,$referrer) {
	
	if (strstr($referrer,"list")) $list = TRUE;
	if (strstr($referrer,"entries")) $list = FALSE;
	if (($user_level <= 1) && ($go == "entries") && ($list == FALSE)) $output = "admin";
	elseif (($user_level <= 1) && ($go == "entries") && ($list == TRUE)) $output = "list";
	else $output = "list";
	return $output;
	
}
if (($action == "add") && ($remaining_entries == 0)) echo "<div class='error'>Entry submission disabled. Entry limit reached.</div>";
?>
<form action="<?php echo $base_url; ?>/includes/process.inc.php?section=<?php echo admin_relocate($row_user['userLevel'],$go,$_SERVER['HTTP_REFERER']);?>&amp;action=<?php echo $action; ?>&amp;go=<?php echo $go;?>&amp;dbTable=<?php echo $brewing_db_table; ?>&amp;filter=<?php echo $filter; if ($id != "default") echo "&amp;id=".$id; ?>" method="POST" name="form1" id="form1" onSubmit="return CheckRequiredFields()">
<?php if ($row_user['userLevel'] == 2) { ?>
<input type="hidden" name="brewBrewerID" value="<?php echo $row_user['id']; ?>">
<input type="hidden" name="brewBrewerFirstName" value="<?php echo $row_name['brewerFirstName']; ?>">
<input type="hidden" name="brewBrewerLastName" value="<?php echo $row_name['brewerLastName']; ?>">
<?php } ?> 
<h2>Required Information</h2>
<?php if (($action == "add") && ($remaining_entries == 0)) ?>
<p><input type="submit" class="button" value="Submit Entry" alt="Submit Entry" <?php if (($action == "add") && ($remaining_entries == 0)) echo "DISABLED"; ?> /></p>
<table>
<?php
if (($filter == "admin") || ($filter == "default")) $brewer_id = $row_user['id']; else $brewer_id = $filter; 

$query_brewer = sprintf("SELECT uid,brewerFirstName,brewerLastName FROM $brewer_db_table WHERE uid='%s'",$brewer_id);
$brewer = mysql_query($query_brewer, $brewing) or die(mysql_error());
$row_brewer = mysql_fetch_assoc($brewer);

?>
<tr>
   <td class="dataLabel">Brewer:</td>
   <td class="data">
   <?php echo $row_brewer['brewerFirstName']." ".$row_brewer['brewerLastName']; ?>
   <input type="hidden" name="brewBrewerID" value="<?php echo $row_brewer['uid']; ?>">
   </td>
   <td class="data">&nbsp;</td>
</tr>
<tr>
  <td class="dataLabel">Co-Brewer:</td>
  <td class="data"><input type="text"  name="brewCoBrewer" value="<?php if (($action == "add") && ($remaining_entries == 0)) echo "Disabled - Entry Limit Reached"; if ($action == "edit") echo $row_log['brewCoBrewer']; ?>" size="30" <?php if (($action == "add") && ($remaining_entries == 0)) echo "DISABLED"; ?>></td>
</tr>
<tr>
   <td class="dataLabel">Entry Name:</td>
   <td class="data"><input type="text"  name="brewName" value="<?php if (($action == "add") && ($remaining_entries == 0)) echo "Disabled - Entry Limit Reached"; if ($action == "edit") echo $row_log['brewName']; ?>" size="30" <?php if (($action == "add") && ($remaining_entries == 0)) echo "DISABLED"; ?>></td>
   <td class="data"><?php if (!NHC) { ?><span class="required">Required for all entries</span><?php } ?></td>
</tr>
<tr>
   <td class="dataLabel">Style:</td>
   <td class="data">
   
   <select name="brewStyle" id="type">
   	 <option value=""></option>
	 <?php
	do { 
	if ($row_prefs['prefsUserSubCatLimit'] == "") { ?>
		<option value="<?php echo $style_value; ?>" <?php if (($action == "edit") && ($row_styles['brewStyleGroup'].$row_styles['brewStyleNum'] == $row_log['brewCategorySort'].$row_log['brewSubCategory'])) echo "SELECTED"; ?>><?php echo ltrim($row_styles['brewStyleGroup'], "0"); echo $row_styles['brewStyleNum']." ".$row_styles['brewStyle']; ?></option>
	<?php	
	}
	else {
	$style_value = ltrim($row_styles['brewStyleGroup'], "0")."-".$row_styles['brewStyleNum'];
	
	if ($row_user['userLevel'] == 2) $subcat_limit = limit_subcategory($style_value,$row_prefs['prefsUserSubCatLimit'],$row_prefs['prefsUSCLExLimit'],$row_prefs['prefsUSCLEx'],$row_user['id']);
	elseif (($row_user['userLevel'] <= 1) && ($filter != "admin") && ($id == "default")) $subcat_limit = limit_subcategory($style_value,$row_prefs['prefsUserSubCatLimit'],$row_prefs['prefsUSCLExLimit'],$row_prefs['prefsUSCLEx'],$filter);
	elseif (($row_user['userLevel'] <= 1) && ($filter != "admin") && ($id != "default")) $subcat_limit = limit_subcategory($style_value,$row_prefs['prefsUserSubCatLimit'],$row_prefs['prefsUSCLExLimit'],$row_prefs['prefsUSCLEx'],$row_log['brewBrewerID']);
	elseif (($row_user['userLevel'] <= 1) && ($filter == "admin")) $subcat_limit = limit_subcategory($style_value,$row_prefs['prefsUserSubCatLimit'],$row_prefs['prefsUSCLExLimit'],$row_prefs['prefsUSCLEx'],$row_user['id']);
	//else $subcat_limit = FALSE;
	?>
   <option value="<?php echo $style_value; ?>" <?php if ($action == "edit") { 
	   if ($row_styles['brewStyleGroup'].$row_styles['brewStyleNum'] == $row_log['brewCategorySort'].$row_log['brewSubCategory']) echo "SELECTED"; 
	   if ($row_styles['brewStyleGroup'].$row_styles['brewStyleNum'] != $row_log['brewCategorySort'].$row_log['brewSubCategory']) echo $subcat_limit; 
   } 
   if (($action == "add") && ($remaining_entries > 0)) echo $subcat_limit; if (($action == "add") && ($remaining_entries == 0)) echo "DISABLED";?>><?php echo ltrim($row_styles['brewStyleGroup'], "0"); echo $row_styles['brewStyleNum']." ".$row_styles['brewStyle']; if ($action == "edit") { if (($row_styles['brewStyleGroup'].$row_styles['brewStyleNum'] != $row_log['brewCategorySort'].$row_log['brewSubCategory']) && ($subcat_limit == "DISABLED")) echo " [disabled - subcategory entry limit reached]"; } if (($action == "add") && ($subcat_limit == "DISABLED")) echo " [disabled - subcategory entry limit reached]"; ?></option>
    <?php }
	} while ($row_styles = mysql_fetch_assoc($styles)); 

	?>
   </select>
   </td>
   <td class="data"><span class="required">Required for all entries</span><span class="icon"><img src="<?php echo $base_url; ?>/images/information.png" /></span><a id="modal_window_link" href="<?php echo $base_url; ?>/output/styles.php">View Accepted Styles</a></td>
</tr>
</table>
<div id="special">
<table>
<tr>
   <td class="dataLabel">Special Ingredients and/or Classic Style:</td>
</tr>
<tr>
   <td class="dataLeft">
   	<span class="required"><em>Required for categories 6D, 16E, 17F, 20, 21, 22B, 22C, 23, 25C, 26A, 26C, 27E, 28B-D, and all custom styles.</em></span><br />
    <span class="required"><em>Base style required for categories 20, 21, and 22B</em>.</span> Specify if the entry is based on a classic style (e.g., Blonde Ale or Belgian Tripel). Otherwise, more general categories are acceptable (e.g., &ldquo;wheat ale&rdquo; or &ldquo;porter&rdquo;). 
    <ul>
    	<li>50 character limit - use keywords and abbreviations.</li>
    	<li>Enter the base style (if appropriate) and specialty nature of your beer/mead/cider in the following format: <em>base style, special nature</em>.
            <ul>
                <li>Beer example: <em>robust porter, clover honey, sour cherries</em> or <em>wheat ale, anaheim/jalape&ntilde;o chiles</em>, etc.</li>
                <li>Mead example: <em>wildflower honey, blueberries</em> or <em>traditional tej with gesho</em>, etc.</li>
                <li>Cider example: <em>golden russet apples, clove, cinnamon</em> or <em>strawberry and rhubarb</em>, etc.</li>
            </ul>
        </li>
    </ul>
  </td>
</tr>
<tr>
   <td class="dataLeft"><input type="text" <?php if (highlight_required($msg,"1")) echo "class=\"special-required\"";  ?> name="brewInfo" id="brewInfo" value="<?php if ($action == "edit") echo $row_log['brewInfo']; ?>" maxlength="50" size="50">
   </td>
</tr>
<tr>
   <td class="dataLeft">Characters remaining: <span id="count" style="font-weight:bold">50</span></td>
</tr>
</table>
</div>
<div id="mead-cider" <?php if (highlight_required($msg,"2")) echo "class=\"special-required\""; ?>>
<table>
<tr>
   <td class="dataLabel" colspan="2">For Mead and Cider:</td>
</tr>
<tr>
   <td class="dataLeft" colspan="2"><em>Required for categories 24, 25, 26, 27, and 28</em></td>
</tr>
<tr>
   <td class="dataLeft">Carbonation (Choose ONE):</td>
   <td class="dataLeft">Sweetness (Choose ONE):</td>
</tr>
<tr>
   <td class="data"><input type="radio" name="brewMead1" value="Still" id="brewMead1_0" <?php if (($action == "edit") && ($row_log['brewMead1'] == "Still")) echo "CHECKED"; ?>/> Still<br /><input type="radio" name="brewMead1" value="Petillant" id="brewMead1_1"  <?php if (($action == "edit") && ($row_log['brewMead1'] == "Petillant")) echo "CHECKED"; ?>/> Petillant<br /><input type="radio" name="brewMead1" value="Sparkling" id="brewMead1_2"  <?php if (($action == "edit") && ($row_log['brewMead1'] == "Sparkling")) echo "CHECKED"; ?>/> Sparkling</td>
   <td class="data"><input type="radio" name="brewMead2" value="Dry" id="brewMead2_0"  <?php if (($action == "edit") && ($row_log['brewMead2'] == "Dry")) echo "CHECKED"; ?> /> Dry<br /><input type="radio" name="brewMead2" value="Semi-Sweet" id="brewMead2_1"  <?php if (($action == "edit") && ($row_log['brewMead2'] == "Semi-Sweet")) echo "CHECKED"; ?>/> Semi-Sweet<br /><input type="radio" name="brewMead2" value="Sweet" id="brewMead2_2"  <?php if (($action == "edit") && ($row_log['brewMead2'] == "Sweet")) echo "CHECKED"; ?>/> Sweet</td>
</tr>
</table>
</div>
<div id="mead" <?php if (highlight_required($msg,"3")) echo "class=\"special-required\"" ?>>
<table>
<tr>
   <td class="dataLabel" colspan="2">For Mead:</td>
</tr>
<tr>
   <td class="dataLeft" colspan="2"><em>Required for categories 24, 25, and 26</em></td>
</tr>
<tr>
   <td class="dataLeft" colspan="2">Strength (Choose ONE):</td>
</tr>
<tr>
   <td class="data"><input type="radio" name="brewMead3" value="Hydromel" id="brewMead3_0"  <?php if (($action == "edit") && ($row_log['brewMead3'] == "Hydromel")) echo "CHECKED"; ?> /> Hydromel (light)<br /><input type="radio" name="brewMead3" value="Standard" id="brewMead3_1"  <?php if (($action == "edit") && ($row_log['brewMead3'] == "Standard")) echo "CHECKED"; ?> /> Standard<br /><input type="radio" name="brewMead3" value="Sack" id="brewMead3_2"  <?php if (($action == "edit") && ($row_log['brewMead3'] == "Sack")) echo "CHECKED"; ?> /> Sack (strong)</td>
   <td>&nbsp;</td>
</tr>
</table>
</div>
<?php if ($row_prefs['prefsHideRecipe'] == "N") { ?>
<h2>Optional Information</h2>
<p>The information below is not required to process your entry. However, the more information you provide about your entry, the more complete the required entry documentation will be.</p>
<p>Click the headings below to expand and collapse each category.</p>
<div id="menu_container"> 
<div id="outer"> 
<div class="menus">
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>General</h4>
<div class="toggle_container">
<table>
<tr>
   <td class="dataLabel">Amount Brewed:</td>
   <td class="data"><input name="brewYield" type="text" size="10" tooltipText="<?php echo $toolTip_decimal; ?>" value="<?php if ($action == "edit") echo $row_log['brewYield']; ?>">&nbsp;<?php echo $row_prefs['prefsLiquid2']; ?></td>
   <td class="data">&nbsp;</td>
</tr>
<tr>
  <td class="dataLabel">Co-Brewer Name:</td>
  <td class="data"><input type="text"  name="brewCoBrewer" value="<?php if ($action == "edit") echo $row_log['brewCoBrewer']; ?>" size="30"></td>
  <td class="data">&nbsp;</td>
</tr>
</table>
<table>
    <tr>
       <td class="dataLabel">Brewer's Specifics:</td>
    </tr>
    <tr>
   		<td class="dataLeft"><em>Only use to record special procedures, brewing techniques, etc.</em></td>
	</tr>
    <tr>
    	<td><textarea name="brewComments" cols="60" rows="5" id="brewComments"><?php if ($action == "edit") echo $row_log['brewComments']; ?></textarea></td>
    </tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Dates</h4>
<div class="toggle_container">
<table>
<tr>
  <td class="dataLabel">Brewing Date:</td>
  <td class="data"><input type="text" id="brewDate"  name="brewDate" value="<?php if ($action == "edit") echo $row_log['brewDate']; ?>" size="20">&nbsp;YYYY-MM-DD</td>
</tr>
<tr>
  <td class="dataLabel">Bottling Date:</td>
  <td class="data"><input type="text" id="brewBottleDate" name="brewBottleDate" value="<?php if ($action == "edit") echo $row_log['brewBottleDate']; ?>" size="20">&nbsp;YYYY-MM-DD</td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Specific Gravities</h4>
<div class="toggle_container">
<table>
<tr>
   <td  class="dataLabel">Original:</td>
   <td class="data"><input name="brewOG" type="text" size="10" tooltipText="<?php echo $toolTip_gravity; ?>" value="<?php if ($action == "edit") echo $row_log['brewOG']; ?>"></td>
</tr>
<tr>
   <td class="dataLabel">Final:</td>
   <td class="data"><input name="brewFG" type="text" size="10" tooltipText="<?php echo $toolTip_gravity; ?>" value="<?php if ($action == "edit") echo $row_log['brewFG']; if ($action == "importCalc") echo round ($brewFG, 3); ?>"></td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Fermentables: Malt Extracts</h4>
<div class="toggle_container">
<table>
<tr>
   <td class="dataLabel">Extract 1:</td>
   <td class="data"><input name="brewExtract1" type="text" value="<?php if ($action == "edit") echo $row_log['brewExtract1']; ?>"></td>
   <td class="dataLabel">Weight:</td>
   <td class="data"><input name="brewExtract1Weight" type="text" id="brewExtract1Weight" tooltipText="<?php echo $toolTip_decimal; ?>" value="<?php if ($action == "edit") echo $row_log['brewExtract1Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewExtract1Use" id="brewExtract1Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract1Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract1Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract1Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Extract 2:</td>
   <td class="data"><input name="brewExtract2" type="text" value="<?php if ($action == "edit") echo $row_log['brewExtract2']; ?>"></td>
   <td class="dataLabel">Weight: </td>
   <td class="data"><input name="brewExtract2Weight" type="text" id="brewExtract2Weight" value="<?php if ($action == "edit") echo $row_log['brewExtract2Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewExtract2Use" id="brewExtract2Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract2Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract2Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract2Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Extract 3:</td>
   <td class="data"><input name="brewExtract3" type="text" value="<?php if ($action == "edit") echo $row_log['brewExtract3']; ?>"></td>
   <td class="dataLabel">Weight: </td>
   <td class="data"><input name="brewExtract3Weight" type="text" id="brewExtract3Weight" value="<?php if ($action == "edit") echo $row_log['brewExtract3Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewExtract3Use" id="brewExtract3Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract3Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract3Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract3Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Extract 4:</td>
   <td class="data"><input name="brewExtract4" type="text" value="<?php if ($action == "edit") echo $row_log['brewExtract4']; ?>"></td>
   <td class="dataLabel">Weight: </td>
   <td class="data"><input name="brewExtract4Weight" type="text" id="brewExtract4Weight" value="<?php if ($action == "edit") echo $row_log['brewExtract4Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewExtract4Use" id="brewExtract4Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract4Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract4Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract4Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Extract 5:</td>
   <td class="data"><input name="brewExtract5" type="text" value="<?php if ($action == "edit") echo $row_log['brewExtract5']; ?>"></td>
   <td class="dataLabel">Weight:</td>
   <td class="data"><input name="brewExtract5Weight" type="text" id="brewExtract5Weight" value="<?php if ($action == "edit") echo $row_log['brewExtract5Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewExtract5Use" id="brewExtract5Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract5Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract5Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewExtract5Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Fermentables: Grain</h4>
<div class="toggle_container">
<table>
<tr>
   <td class="dataLabel">Grain 1:</td>
   <td class="data"><input name="brewGrain1" type="text" id="brewGrain1" value="<?php if ($action == "edit") echo $row_log['brewGrain1']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input name="brewGrain1Weight" type="text" id="brewGrain1Weight" tooltipText="<?php echo $toolTip_decimal; ?>" value="<?php if ($action == "edit") echo $row_log['brewGrain1Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewGrain1Use" id="brewGrain1Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain1Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain1Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain1Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Grain 2:</td>
   <td class="data"><input name="brewGrain2" type="text" id="brewGrain2" value="<?php if ($action == "edit") echo $row_log['brewGrain2']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input name="brewGrain2Weight" type="text" id="brewGrain1Weight" value="<?php if ($action == "edit") echo $row_log['brewGrain2Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewGrain2Use" id="brewGrain2Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain2Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain2Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain2Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Grain 3:</td>
   <td class="data"><input name="brewGrain3" type="text" id="brewGrain3" value="<?php if ($action == "edit") echo $row_log['brewGrain3']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input name="brewGrain3Weight" type="text" id="brewGrain1Weight" value="<?php if ($action == "edit") echo $row_log['brewGrain3Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewGrain3Use" id="brewGrain3Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain3Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain3Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain3Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Grain 4:</td>
   <td class="data"><input name="brewGrain4" type="text" id="brewGrain4" value="<?php if ($action == "edit") echo $row_log['brewGrain4']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input name="brewGrain4Weight" type="text" id="brewGrain1Weight" value="<?php if ($action == "edit") echo $row_log['brewGrain4Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewGrain4Use" id="brewGrain4Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain4Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain4Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain4Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Grain 5:</td>
   <td class="data"><input name="brewGrain5" type="text" id="brewGrain5" value="<?php if ($action == "edit") echo $row_log['brewGrain5']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input name="brewGrain5Weight" type="text" id="brewGrain1Weight" value="<?php if ($action == "edit") echo $row_log['brewGrain5Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewGrain5Use" id="brewGrain5Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain5Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain5Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain5Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Grain 6:</td>
   <td class="data"><input name="brewGrain6" type="text" id="brewGrain6" value="<?php if ($action == "edit") echo $row_log['brewGrain6']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input name="brewGrain6Weight" type="text" id="brewGrain6Weight" value="<?php if ($action == "edit") echo $row_log['brewGrain6Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewGrain6Use" id="brewGrain6Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain6Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain6Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain6Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Grain 7:</td>
   <td class="data"><input name="brewGrain7" type="text" id="brewGrain7" value="<?php if ($action == "edit") echo $row_log['brewGrain7']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input name="brewGrain7Weight" type="text" id="brewGrain1Weight" value="<?php if ($action == "edit") echo $row_log['brewGrain7Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewGrain7Use" id="brewGrain7Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain7Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain7Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain7Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Grain 8:</td>
   <td class="data"><input name="brewGrain8" type="text" id="brewGrain8" value="<?php if ($action == "edit") echo $row_log['brewGrain8']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input name="brewGrain8Weight" type="text" id="brewGrain1Weight" value="<?php if ($action == "edit") echo $row_log['brewGrain8Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewGrain8Use" id="brewGrain8Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain8Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain8Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain8Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Grain 9:</td>
   <td class="data"><input name="brewGrain9" type="text" id="brewGrain9" value="<?php if ($action == "edit") echo $row_log['brewGrain9']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input name="brewGrain9Weight" type="text" id="brewGrain1Weight" value="<?php if ($action == "edit") echo $row_log['brewGrain9Weight']; ?>" size="5">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewGrain9Use" id="brewGrain9Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain9Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain9Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewGrain9Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Fermentables: Adjuncts</h4>
<div class="toggle_container">
<table>
<tr>
   <td class="dataLabel">Adjunct 1:</td>
   <td class="data"><input name="brewAddition1" type="text" id="brewAddition1" value="<?php if ($action == "edit") echo $row_log['brewAddition1']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input name="brewAddition1Amt" type="text" tooltipText="<?php echo $toolTip_decimal; ?>" value="<?php if ($action == "edit") echo $row_log['brewAddition1Amt']; if ($action == "importCalc") echo $brewAdjunct1Weight; ?>" size="10" maxlength="20">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewAddition1Use" id="brewAddition1Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition1Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition1Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition1Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Adjunct 2:</td>
   <td class="data"><input name="brewAddition2" type="text" id="brewAddition2" value="<?php if ($action == "edit") echo $row_log['brewAddition2']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input type="text" name="brewAddition2Amt" value="<?php if ($action == "edit") echo $row_log['brewAddition2Amt']; if ($action == "importCalc") echo $brewAdjunct2Weight; ?>" size="10" maxlength="20">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewAddition2Use" id="brewAddition2Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition2Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition2Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition2Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Adjunct 3:</td>
   <td class="data"><input name="brewAddition3" type="text" id="brewAddition3" value="<?php if ($action == "edit") echo $row_log['brewAddition3']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input type="text" name="brewAddition3Amt" value="<?php if ($action == "edit") echo $row_log['brewAddition3Amt']; if ($action == "importCalc") echo $brewAdjunct3Weight; ?>" size="10" maxlength="20">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewAddition3Use" id="brewAddition3Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition3Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition3Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition3Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Adjunct 4:</td>
   <td class="data"><input name="brewAddition4" type="text" id="brewAddition4" value="<?php if ($action == "edit") echo $row_log['brewAddition4']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input type="text" name="brewAddition4Amt" value="<?php if ($action == "edit") echo $row_log['brewAddition4Amt']; if ($action == "importCalc") echo $brewAdjunct4Weight; ?>" size="10" maxlength="20">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewAddition4Use" id="brewAddition4Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition4Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition4Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition4Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Adjunct 5:</td>
   <td class="data"><input name="brewAddition5" type="text" id="brewAddition5" value="<?php if ($action == "edit") echo $row_log['brewAddition5']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input type="text" name="brewAddition5Amt" value="<?php if ($action == "edit") echo $row_log['brewAddition5Amt']; if ($action == "importCalc") echo $brewAdjunct5Weight; ?>" size="10" maxlength="20">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewAddition5Use" id="brewAddition5Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition5Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition5Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition5Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Adjunct 6:</td>
   <td class="data"><input name="brewAddition6" type="text" id="brewAddition6" value="<?php if ($action == "edit") echo $row_log['brewAddition6']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input type="text" name="brewAddition6Amt" value="<?php if ($action == "edit") echo $row_log['brewAddition6Amt']; if ($action == "importCalc") echo $brewAdjunct6Weight; ?>" size="10" maxlength="20">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewAddition6Use" id="brewAddition6Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition6Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition6Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition6Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Adjunct 7:</td>
   <td class="data"><input name="brewAddition7" type="text" id="brewAddition7" value="<?php if ($action == "edit") echo $row_log['brewAddition7']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input type="text" name="brewAddition7Amt" value="<?php if ($action == "edit") echo $row_log['brewAddition7Amt']; if ($action == "importCalc") echo $brewAdjunct7Weight; ?>" size="10" maxlength="20">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewAddition7Use" id="brewAddition7Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition7Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition7Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition7Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
<td class="dataLabel">Adjunct 8:</td>
   <td class="data"><input name="brewAddition8" type="text" id="brewAddition8" value="<?php if ($action == "edit") echo $row_log['brewAddition8']; ?>"></td> 
   <td class="dataLabel">Weight:</td>
   <td class="data"><input type="text" name="brewAddition8Amt" value="<?php if ($action == "edit") echo $row_log['brewAddition8Amt']; if ($action == "importCalc") echo $brewAdjunct8Weight; ?>" size="10" maxlength="20">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewAddition8Use" id="brewAddition8Use">
			<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition8Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition8Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition8Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
<tr>
	<td class="dataLabel">Adjunct 9:</td>
   	<td class="data"><input name="brewAddition9" type="text" id="brewAddition9" value="<?php if ($action == "edit") echo $row_log['brewAddition9']; ?>"></td> 
    <td class="dataLabel">Weight:</td>
   	<td class="data"><input type="text" name="brewAddition9Amt" value="<?php if ($action == "edit") echo $row_log['brewAddition9Amt']; if ($action == "importCalc") echo $brewAdjunct9Weight; ?>" size="10" maxlength="20">&nbsp;<?php echo $row_prefs['prefsWeight2']; ?></td>
   <td class="dataLabel">Use:</td>
   <td class="data">
   		<select name="brewAddition9Use" id="brewAddition9Use">
        	<option value=""></option>
			<option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition9Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        	<option value="Steep" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition9Use'], "Steep"))) {echo "SELECTED";} }?>>Steep</option>
        	<option value="Other" <?php if ($action == "edit") { if (!(strcmp($row_log['brewAddition9Use'], "Other"))) {echo "SELECTED";} }?>>Other</option>
        </select>
   </td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Hops</h4>
<div class="toggle_container">
<table>
<tr>
   <td>&nbsp;</td>
   <td class="dataLabel data">Name</td>
   <td class="dataLabel data">Wt</td>
   <td class="dataLabel data">AAU</td>
   <td class="dataLabel data">Time</td>
   <td class="dataLabel data">Use</td>
   <td class="dataLabel data">Type</td>
   <td class="dataLabel data">Form</td>
</tr>
<tr>
   <td class="dataLabel">Hop 1:</td>
   <td class="data"><input name="brewHops1" type="text" id="brewHops1" value="<?php if ($action == "edit") echo $row_log['brewHops1']; ?>"></td> 
   <td class="data"><input name="brewHops1Weight" type="text" id="brewHops1Weight" size="5" tooltipText="<?php echo $toolTip_decimal; ?>" value="<?php if ($action == "edit") echo $row_log['brewHops1Weight']; if ($action == "importCalc") echo $brewHops1Weight; ?>">&nbsp;<?php echo $row_prefs['prefsWeight1']; ?></td>
     <td class="data"><input name="brewHops1IBU" type="text" id="brewHops1IBU" size="5" tooltipText="<?php echo $toolTip_decimal; ?>" value="<?php if ($action == "edit") echo $row_log['brewHops1IBU']; if ($action == "importCalc") echo $brewHops1IBU; ?>">&nbsp;%</td>
     <td class="data"><input type="text" name="brewHops1Time" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops1Time'];  if ($action == "importCalc") echo $brewHops1Time; ?>">&nbsp;min.</td>
     <td class="data"><select name="brewHops1Use" id="brewHops1Use">
                    <option value=""></option>
		<option value="Boil" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops1Use'], "Boil"))) {echo "SELECTED";} }?>>Boil</option>
        <option value="Dry Hop" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops1Use'], "Dry Hop"))) {echo "SELECTED";} }?>>Dry Hop</option>
        <option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops1Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops1Use'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="First Wort" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops1Use'], "First Wort"))) {echo "SELECTED";} }?>>First Wort</option>
                  </select></td>
    <td class="data"><select name="brewHops1Type" id="brewHops1Type">
                    <option value=""></option>
		<option value="Bittering" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops1Type'], "Bittering"))) {echo "SELECTED";} }?>>Bittering</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops1Type'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="Both" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops1Type'], "Both"))) {echo "SELECTED";} }?>>Both</option>
    </select></td>
   <td class="data"><select name="brewHops1Form" id="brewHops1Form">
                    <option value=""></option>
		<option value="Pellets" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops1Form'], "Pellets"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops1Form, "Pellets"))) {echo "SELECTED";} } ?>>Pellets</option>
        <option value="Plug" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops1Form'], "Plug"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops1Form, "Plug"))) {echo "SELECTED";} } ?>>Plug</option>
        <option value="Leaf" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops1Form'], "Leaf"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops1Form, "Leaf"))) {echo "SELECTED";} } ?>>Leaf</option>
                    </select>
                    
    </td>
  </tr>
  <tr>
   <td class="dataLabel">Hop 2:</td>
   <td class="data"><input name="brewHops2" type="text" id="brewHops2" value="<?php if ($action == "edit") echo $row_log['brewHops2']; ?>"></td> 
   <td class="data"><input name="brewHops2Weight" type="text" id="brewHops2Weight" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops2Weight']; if ($action == "importCalc") echo $brewHops2Weight; ?>">&nbsp;<?php echo $row_prefs['prefsWeight1']; ?></td>
   <td class="data"><input name="brewHops2IBU" type="text" id="brewHops2IBU" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops2IBU']; if ($action == "importCalc") echo $brewHops2IBU; ?>">&nbsp;%</td>
   <td class="data"><input type="text" name="brewHops2Time" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops2Time']; if ($action == "importCalc") echo $brewHops2Time; ?>">&nbsp;min.</td>
   <td class="data"><select name="brewHops2Use" id="brewHops2Use">
                    <option value=""></option>
		<option value="Boil" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops2Use'], "Boil"))) {echo "SELECTED";} }?>>Boil</option>
        <option value="Dry Hop" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops2Use'], "Dry Hop"))) {echo "SELECTED";} }?>>Dry Hop</option>
        <option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops2Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops2Use'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="First Wort" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops2Use'], "First Wort"))) {echo "SELECTED";} }?>>First Wort</option>
                  </select>
   </td>
   <td class="data"><select name="brewHops2Type" id="brewHops2Type">
                    <option value=""></option>
		<option value="Bittering" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops2Type'], "Bittering"))) {echo "SELECTED";} }?>>Bittering</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops2Type'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="Both" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops2Type'], "Both"))) {echo "SELECTED";} }?>>Both</option>
    </select></td>
   <td class="data"><select name="brewHops2Form" id="brewHops2Form">
                    <option value=""></option>
		<option value="Pellets" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops2Form'], "Pellets"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops2Form, "Pellets"))) {echo "SELECTED";} } ?>>Pellets</option>
        <option value="Plug" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops2Form'], "Plug"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops2Form, "Plug"))) {echo "SELECTED";} } ?>>Plug</option>
        <option value="Leaf" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops2Form'], "Leaf"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops2Form, "Leaf"))) {echo "SELECTED";} } ?>>Leaf</option>
                    </select>
</td>
</tr>
<tr>
   <td class="dataLabel">Hop 3:</td>
   <td class="data"><input name="brewHops3" type="text" id="brewHops3" value="<?php if ($action == "edit") echo $row_log['brewHops3']; ?>"></td> 
   <td class="data"><input name="brewHops3Weight" type="text" id="brewHops3Weight" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops3Weight']; if ($action == "importCalc") echo $brewHops3Weight; ?>">&nbsp;<?php echo $row_prefs['prefsWeight1']; ?></td>
   <td class="data"><input name="brewHops3IBU" type="text" id="brewHops3IBU" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops3IBU']; if ($action == "importCalc") echo $brewHops3IBU; ?>">&nbsp;%</td>
   <td class="data"><input type="text" name="brewHops3Time" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops3Time']; if ($action == "importCalc") echo $brewHops3Time; ?>">&nbsp;min.</td>
   <td class="data"><select name="brewHops3Use" id="brewHops3Use">
                    <option value=""></option>
		<option value="Boil" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops3Use'], "Boil"))) {echo "SELECTED";} }?>>Boil</option>
        <option value="Dry Hop" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops3Use'], "Dry Hop"))) {echo "SELECTED";} }?>>Dry Hop</option>
        <option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops3Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops3Use'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="First Wort" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops3Use'], "First Wort"))) {echo "SELECTED";} }?>>First Wort</option>
                  </select>
   </td>
   <td class="data"><select name="brewHops3Type" id="brewHops3Type">
                    <option value=""></option>
		<option value="Bittering" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops3Type'], "Bittering"))) {echo "SELECTED";} }?>>Bittering</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops3Type'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="Both" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops3Type'], "Both"))) {echo "SELECTED";} }?>>Both</option>
                  </select>
   </td>
   <td class="data"><select name="brewHops3Form" id="brewHops3Form">
                    <option value=""></option>
		<option value="Pellets" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops3Form'], "Pellets"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops3Form, "Pellets"))) {echo "SELECTED";} } ?>>Pellets</option>
        <option value="Plug" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops3Form'], "Plug"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops3Form, "Plug"))) {echo "SELECTED";} } ?>>Plug</option>
        <option value="Leaf" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops3Form'], "Leaf"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops3Form, "Leaf"))) {echo "SELECTED";} } ?>>Leaf</option>
                    </select>
	</td>
</tr>
<tr>
   <td class="dataLabel">Hop 4:</td>
   <td class="data"><input name="brewHops4" type="text" id="brewHops4" value="<?php if ($action == "edit") echo $row_log['brewHops4']; ?>"></td> 
   <td class="data"><input name="brewHops4Weight" type="text" id="brewHops4Weight" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops4Weight']; if ($action == "importCalc") echo $brewHops4Weight; ?>">&nbsp;<?php echo $row_prefs['prefsWeight1']; ?></td>
   <td class="data"><input name="brewHops4IBU" type="text" id="brewHops4IBU" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops4IBU']; if ($action == "importCalc") echo $brewHops4IBU; ?>">&nbsp;%</td>
   <td class="data"><input type="text" name="brewHops4Time" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops4Time']; if ($action == "importCalc") echo $brewHops4Time; ?>">&nbsp;min.</td>
   <td class="data"><select name="brewHops4Use" id="brewHops4Use">
                    <option value=""></option>
		<option value="Boil" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops4Use'], "Boil"))) {echo "SELECTED";} }?>>Boil</option>
        <option value="Dry Hop" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops4Use'], "Dry Hop"))) {echo "SELECTED";} }?>>Dry Hop</option>
        <option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops4Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops4Use'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="First Wort" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops4Use'], "First Wort"))) {echo "SELECTED";} }?>>First Wort</option>
                  </select>
   </td>
   <td class="data"><select name="brewHops4Type" id="brewHops4Type">
                    <option value=""></option>
		<option value="Bittering" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops4Type'], "Bittering"))) {echo "SELECTED";} }?>>Bittering</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops4Type'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="Both" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops4Type'], "Both"))) {echo "SELECTED";} }?>>Both</option>
                    </select>
    </td>
     <td class="data"><select name="brewHops4Form" id="brewHops4Form">
                   <option value=""></option>
		<option value="Pellets" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops4Form'], "Pellets"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops4Form, "Pellets"))) {echo "SELECTED";} } ?>>Pellets</option>
        <option value="Plug" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops4Form'], "Plug"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops4Form, "Plug"))) {echo "SELECTED";} } ?>>Plug</option>
        <option value="Leaf" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops4Form'], "Leaf"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops4Form, "Leaf"))) {echo "SELECTED";} } ?>>Leaf</option>
                    </select>
	</td>
</tr>
<tr>
   <td class="dataLabel">Hop 5:</td>
   <td class="data"><input name="brewHops5" type="text" id="brewHops5" value="<?php if ($action == "edit") echo $row_log['brewHops5']; ?>"></td> 
   <td class="data"><input name="brewHops5Weight" type="text" id="brewHops5Weight" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops5Weight']; if ($action == "importCalc") echo $brewHops5Weight; ?>">&nbsp;<?php echo $row_prefs['prefsWeight1']; ?></td>
   <td class="data"><input name="brewHops5IBU" type="text" id="brewHops5IBU" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops5IBU']; if ($action == "importCalc") echo $brewHops5IBU; ?>">&nbsp;%</td>
   <td class="data"><input type="text" name="brewHops5Time" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops5Time']; if ($action == "importCalc") echo $brewHops5Time; ?>">&nbsp;min.</td>
   <td class="data"><select name="brewHops5Use" id="brewHops5Use">
                    <option value=""></option>
		<option value="Boil" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops5Use'], "Boil"))) {echo "SELECTED";} }?>>Boil</option>
        <option value="Dry Hop" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops5Use'], "Dry Hop"))) {echo "SELECTED";} }?>>Dry Hop</option>
        <option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops5Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops5Use'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="First Wort" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops5Use'], "First Wort"))) {echo "SELECTED";} }?>>First Wort</option>
                     </select>
   </td>
   <td class="data"><select name="brewHops5Type" id="brewHops5Type">
                    <option value=""></option>
		<option value="Bittering" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops5Type'], "Bittering"))) {echo "SELECTED";} }?>>Bittering</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops5Type'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="Both" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops5Type'], "Both"))) {echo "SELECTED";} }?>>Both</option>
                    </select>
   </td>
   <td class="data"><select name="brewHops5Form" id="brewHops5Form">
                    <option value=""></option>
		<option value="Pellets" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops5Form'], "Pellets"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops5Form, "Pellets"))) {echo "SELECTED";} } ?>>Pellets</option>
        <option value="Plug" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops5Form'], "Plug"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops5Form, "Plug"))) {echo "SELECTED";} } ?>>Plug</option>
        <option value="Leaf" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops5Form'], "Leaf"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops5Form, "Leaf"))) {echo "SELECTED";} } ?>>Leaf</option>
                    </select>
   </td>
</tr>
<tr>
   <td class="dataLabel">Hop 6:</td>
   <td class="data"><input name="brewHops6" type="text" id="brewHops6" value="<?php if ($action == "edit") echo $row_log['brewHops6']; ?>"></td> 
   <td class="data"><input name="brewHops6Weight" type="text" id="brewHops6Weight" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops6Weight']; if ($action == "importCalc") echo $brewHops6Weight; ?>">&nbsp;<?php echo $row_prefs['prefsWeight1']; ?></td>
   <td class="data"><input name="brewHops6IBU" type="text" id="brewHops6IBU" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops6IBU']; if ($action == "importCalc") echo $brewHops6IBU; ?>">&nbsp;%</td>
   <td class="data"><input type="text" name="brewHops6Time" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops6Time']; if ($action == "importCalc") echo $brewHops6Time; ?>">&nbsp;min.</td>
   <td class="data"><select name="brewHops6Use" id="brewHops6Use">
   					<option value=""></option>
        <option value="Boil" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops6Use'], "Boil"))) {echo "SELECTED";} }?>>Boil</option>
        <option value="Dry Hop" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops6Use'], "Dry Hop"))) {echo "SELECTED";} }?>>Dry Hop</option>
        <option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops6Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops6Use'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="First Wort" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops6Use'], "First Wort"))) {echo "SELECTED";} }?>>First Wort</option>
                  </select>
   </td>
   <td class="data"><select name="brewHops6Type" id="brewHops6Type">
                    <option value=""></option>
		<option value="Bittering" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops6Type'], "Bittering"))) {echo "SELECTED";} }?>>Bittering</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops6Type'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="Both" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops6Type'], "Both"))) {echo "SELECTED";} }?>>Both</option>
                    </select>
    </td>
    <td class="data"><select name="brewHops6Form" id="brewHops6Form">
                    <option value=""></option>
		<option value="Pellets" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops6Form'], "Pellets"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops6Form, "Pellets"))) {echo "SELECTED";} } ?>>Pellets</option>
        <option value="Plug" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops6Form'], "Plug"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops6Form, "Plug"))) {echo "SELECTED";} } ?>>Plug</option>
        <option value="Leaf" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops6Form'], "Leaf"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops6Form, "Leaf"))) {echo "SELECTED";} } ?>>Leaf</option>
                    </select>
	</td>
</tr>
<tr>
   <td class="dataLabel">Hop 7:</td>
   <td class="data"><input name="brewHops7" type="text" id="brewHops7" value="<?php if ($action == "edit") echo $row_log['brewHops7']; ?>"></td> 
   <td class="data"><input name="brewHops7Weight" type="text" id="brewHops7Weight" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops7Weight']; if ($action == "importCalc") echo $brewHops7Weight; ?>">&nbsp;<?php echo $row_prefs['prefsWeight1']; ?></td>
     <td class="data"><input name="brewHops7IBU" type="text" id="brewHops7IBU" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops7IBU']; if ($action == "importCalc") echo $brewHops7IBU; ?>">&nbsp;%</td>
     <td class="data"><input type="text" name="brewHops7Time" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops7Time']; if ($action == "importCalc") echo $brewHops7Time; ?>">&nbsp;min.</td>
     <td class="data"><select name="brewHops7Use" id="brewHops7Use">
                    <option value=""></option>
		<option value="Boil" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops7Use'], "Boil"))) {echo "SELECTED";} }?>>Boil</option>
        <option value="Dry Hop" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops7Use'], "Dry Hop"))) {echo "SELECTED";} }?>>Dry Hop</option>
        <option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops7Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops7Use'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="First Wort" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops7Use'], "First Wort"))) {echo "SELECTED";} }?>>First Wort</option>
                  </select>
   </td>
   <td class="data"><select name="brewHops7Type" id="brewHops7Type">
                    <option value=""></option>
		<option value="Bittering" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops7Type'], "Bittering"))) {echo "SELECTED";} }?>>Bittering</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops7Type'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="Both" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops7Type'], "Both"))) {echo "SELECTED";} }?>>Both</option>
                    </select>
    </td>
     <td class="data"><select name="brewHops7Form" id="brewHops7Form">
                    <option value=""></option>
		<option value="Pellets" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops7Form'], "Pellets"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops7Form, "Pellets"))) {echo "SELECTED";} } ?>>Pellets</option>
        <option value="Plug" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops7Form'], "Plug"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops7Form, "Plug"))) {echo "SELECTED";} } ?>>Plug</option>
        <option value="Leaf" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops7Form'], "Leaf"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops7Form, "Leaf"))) {echo "SELECTED";} } ?>>Leaf</option>
                    </select>
	</td>
</tr>
<tr>
   <td class="dataLabel">Hop 8:</td>
   <td class="data"><input name="brewHops8" type="text" id="brewHops8" value="<?php if ($action == "edit") echo $row_log['brewHops8']; ?>"></td> 
   <td class="data"><input name="brewHops8Weight" type="text" id="brewHops8Weight" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops8Weight']; if ($action == "importCalc") echo $brewHops8Weight; ?>">&nbsp;<?php echo $row_prefs['prefsWeight1']; ?></td>
   <td class="data"><input name="brewHops8IBU" type="text" id="brewHops8IBU" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops8IBU']; if ($action == "importCalc") echo $brewHops8IBU; ?>">&nbsp;%</td>
   <td class="data"><input type="text" name="brewHops8Time" size="5"  value="<?php if ($action == "edit") echo $row_log['brewHops8Time']; if ($action == "importCalc") echo $brewHops8Time; ?>">&nbsp;min.</td>
   <td class="data"><select name="brewHops8Use" id="brewHops8Use">
                    <option value=""></option>
		<option value="Boil" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops8Use'], "Boil"))) {echo "SELECTED";} }?>>Boil</option>
        <option value="Dry Hop" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops8Use'], "Dry Hop"))) {echo "SELECTED";} }?>>Dry Hop</option>
        <option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops8Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops8Use'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="First Wort" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops8Use'], "First Wort"))) {echo "SELECTED";} }?>>First Wort</option>
                  </select>
   </td>
   <td class="data"><select name="brewHops8Type" id="brewHops8Type">
                    <option value=""></option>
		<option value="Bittering" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops8Type'], "Bittering"))) {echo "SELECTED";} }?>>Bittering</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops8Type'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="Both" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops8Type'], "Both"))) {echo "SELECTED";} }?>>Both</option>
                    </select>
    </td>
     <td class="data"><select name="brewHops8Form" id="brewHops8Form">
                    <option value=""></option>
		<option value="Pellets" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops8Form'], "Pellets"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops8Form, "Pellets"))) {echo "SELECTED";} } ?>>Pellets</option>
        <option value="Plug" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops8Form'], "Plug"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops8Form, "Plug"))) {echo "SELECTED";} } ?>>Plug</option>
        <option value="Leaf" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops8Form'], "Leaf"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops8Form, "Leaf"))) {echo "SELECTED";} } ?>>Leaf</option>
                    </select>
	</td>
</tr>
<tr>
   <td class="dataLabel">Hop 9:</td>
   <td class="data"><input name="brewHops9" type="text" id="brewHops9" value="<?php if ($action == "edit") echo $row_log['brewHops9']; ?>"></td> 
   <td class="data"><input name="brewHops9Weight" type="text" id="brewHops9Weight" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops9Weight']; if ($action == "importCalc") echo $brewHops9Weight; ?>">&nbsp;<?php echo $row_prefs['prefsWeight1']; ?></td>
   <td class="data"><input name="brewHops9IBU" type="text" id="brewHops9IBU" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops9IBU']; if ($action == "importCalc") echo $brewHops9IBU; ?>">&nbsp;%</td>
   <td class="data"><input type="text" name="brewHops9Time" size="5" value="<?php if ($action == "edit") echo $row_log['brewHops9Time']; if ($action == "importCalc") echo $brewHops9Time; ?>">&nbsp;min.</td>
   <td class="data"><select name="brewHops9Use" id="brewHops9Use">
                    <option value=""></option>
		<option value="Boil" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops9Use'], "Boil"))) {echo "SELECTED";} }?>>Boil</option>
        <option value="Dry Hop" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops9Use'], "Dry Hop"))) {echo "SELECTED";} }?>>Dry Hop</option>
        <option value="Mash" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops9Use'], "Mash"))) {echo "SELECTED";} }?>>Mash</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops9Use'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="First Wort" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops9Use'], "First Wort"))) {echo "SELECTED";} }?>>First Wort</option>
                    </select>
   </td>
   <td class="data"><select name="brewHops9Type" id="brewHops9Type">
                    <option value=""></option>
		<option value="Bittering" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops9Type'], "Bittering"))) {echo "SELECTED";} }?>>Bittering</option>
        <option value="Aroma" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops9Type'], "Aroma"))) {echo "SELECTED";} }?>>Aroma</option>
        <option value="Both" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops9Type'], "Both"))) {echo "SELECTED";} }?>>Both</option>
                    </select>
    </td>
    <td class="data"><select name="brewHops9Form" id="brewHops9Form">
                    <option value=""></option>
		<option value="Pellets" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops9Form'], "Pellets"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops9Form, "Pellets"))) {echo "SELECTED";} } ?>>Pellets</option>
        <option value="Plug" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops9Form'], "Plug"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops9Form, "Plug"))) {echo "SELECTED";} } ?>>Plug</option>
        <option value="Leaf" <?php if ($action == "edit") { if (!(strcmp($row_log['brewHops9Form'], "Leaf"))) {echo "SELECTED";} } if ($action=="importCalc") { if (!(strcmp($brewHops9Form, "Leaf"))) {echo "SELECTED";} } ?>>Leaf</option>
                    </select>
	</td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Mash Schedule</h4>
<div class="toggle_container">
<table>
<tr>
   <td class="dataLabel">Step 1 Name:</td>
   <td class="data"><input name="brewMashStep1Name" type="text" id="brewMashStep1Name" size="30" value="<?php if ($action == "edit") echo $row_log['brewMashStep1Name']; ?>"></td>
   <td class="dataLabel">Time:</td>
   <td class="data"><input name="brewMashStep1Time" type="text" id="brewMashStep1Time" tooltipText="<?php echo $toolTip_decimal; ?>" size="5" value="<?php if ($action == "edit") echo $row_log['brewMashStep1Time']; ?>">&nbsp;min.</td>
   <td class="dataLabel">Temp:</td>
   <td class="data"><input name="brewMashStep1Temp" type="text" id="brewMashStep1Temp" tooltipText="<?php echo $toolTip_decimal; ?>" size="5" value="<?php if ($action == "edit") echo $row_log['brewMashStep1Temp']; ?>">&nbsp;&deg;<?php echo $row_prefs['prefsTemp']; ?></td>
</tr>
<tr>
   <td class="dataLabel">Step 2 Name:</td>
   <td class="data"><input name="brewMashStep2Name" type="text" id="brewMashStep2Name" size="30" value="<?php if ($action == "edit") echo $row_log['brewMashStep2Name']; ?>"></td>
   <td class="dataLabel">Time:</td>
   <td class="data"><input name="brewMashStep2Time" type="text" id="brewMashStep2Time" size="5" value="<?php if ($action == "edit") echo $row_log['brewMashStep2Time']; ?>">&nbsp;min.</td>
   <td class="dataLabel">Temp:</td>
   <td class="data"><input name="brewMashStep2Temp" type="text" id="brewMashStep2Temp" size="5" value="<?php if ($action == "edit") echo $row_log['brewMashStep2Temp']; ?>">&nbsp;&deg;<?php echo $row_prefs['prefsTemp']; ?></td>
</tr>
<tr>
   <td class="dataLabel">Step 3 Name:</td>
   <td class="data"><input name="brewMashStep3Name" type="text" id="brewMashStep3Name" size="30" value="<?php if ($action == "edit") echo $row_log['brewMashStep3Name']; ?>"></td>
   <td class="dataLabel">Time:</td>
   <td class="data"><input name="brewMashStep3Time" type="text" id="brewMashStep3Time" size="5" value="<?php if ($action == "edit") echo $row_log['brewMashStep3Time']; ?>">&nbsp;min.</td>
   <td class="dataLabel">Temp:</td>
   <td class="data"><input name="brewMashStep3Temp" type="text" id="brewMashStep3Temp" size="5" value="<?php if ($action == "edit") echo $row_log['brewMashStep3Temp']; ?>">&nbsp;&deg;<?php echo $row_prefs['prefsTemp']; ?></td>
</tr>
<tr>
   <td class="dataLabel">Step 4 Name:</td>
   <td class="data"><input name="brewMashStep4Name" type="text" id="brewMashStep4Name" size="30" value="<?php if ($action == "edit") echo $row_log['brewMashStep4Name']; ?>"></td>
   <td class="dataLabel">Time:</td>
   <td class="data"><input name="brewMashStep4Time" type="text" id="brewMashStep4Time" size="5" value="<?php if ($action == "edit") echo $row_log['brewMashStep4Time']; ?>">&nbsp;min.</td>
   <td class="dataLabel">Temp:</td>
   <td class="data"><input name="brewMashStep4Temp" type="text" id="brewMashStep4Temp" size="5" value="<?php if ($action == "edit") echo $row_log['brewMashStep4Temp']; ?>">&nbsp;&deg;<?php echo $row_prefs['prefsTemp']; ?></td>
</tr>
<tr>
   <td class="dataLabel">Step 5 Name:</td>
   <td class="data"><input name="brewMashStep5Name" type="text" id="brewMashStep5Name" size="30" value="<?php if ($action == "edit") echo $row_log['brewMashStep5Name']; ?>"></td>
   <td class="dataLabel">Time:</td>
   <td class="data"><input name="brewMashStep5Time" type="text" id="brewMashStep5Time" size="5" value="<?php if ($action == "edit") echo $row_log['brewMashStep5Time']; ?>">&nbsp;min.</td>
   <td class="dataLabel">Temp:</td>
   <td class="data"><input name="brewMashStep5Temp" type="text" id="brewMashStep5Temp" size="5" value="<?php if ($action == "edit") echo $row_log['brewMashStep5Temp']; ?>">&nbsp;&deg;<?php echo $row_prefs['prefsTemp']; ?></td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Water Treatment</h4>
<div class="toggle_container">
<table>
<tr>
   <td class="dataLabel">Type/Amount:</td>
   <td class="data"><textarea name="brewWaterNotes" cols="60" rows="5" id="brewWaterNotes"><?php if ($action == "edit") echo $row_log['brewWaterNotes']; ?></textarea></td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Yeast Culture</h4>
<div class="toggle_container">
<table>
<tr>
   <td class="dataLabel">Name:</td>
   <td class="data"><input type="text" name="brewYeast" size="30" tooltipText="Enter the name of the yeast and the catalog number." value="<?php if ($action == "edit") echo $row_log['brewYeast']; ?>"></td>
   <td class="dataLabel">Manufacturer:</td>
   <td class="data"><input name="brewYeastMan" type="text" id="brewYeastMan" size="30" tooltipText="Indicate the yeast manufacturer's name." value="<?php if ($action == "edit") echo $row_log['brewYeastMan']; ?>"></td>
</tr>
<tr>
   <td class="dataLabel">Form:</td>
   <td class="data">
   <select name="brewYeastForm" id="brewYeastForm">
        <option value=""></option>
	    <option value="Liquid" <?php if ($action == "edit") { if (!(strcmp($row_log['brewYeastForm'], "Liquid"))) {echo "SELECTED";} }?>>Liquid</option>
        <option value="Dry" <?php if ($action == "edit") { if (!(strcmp($row_log['brewYeastForm'], "Dry"))) {echo "SELECTED";} }?>>Dry</option>
   </select>
   </td>
   <td class="dataLabel">Type:</td>
   <td class="data">
   <select name="brewYeastType" id="brewYeastType">
		<option value=""></option>
        <option value="Ale" <?php if ($action == "edit") { if (!(strcmp($row_log['brewYeastType'], "Ale"))) {echo "SELECTED";} }?>>Ale</option>
        <option value="Lager" <?php if ($action == "edit") { if (!(strcmp($row_log['brewYeastType'], "Lager"))) {echo "SELECTED";} }?>>Lager</option>
        <option value="Wheat" <?php if ($action == "edit") { if (!(strcmp($row_log['brewYeastType'], "Wheat"))) {echo "SELECTED";} }?>>Wheat</option>
        <option value="Wine" <?php if ($action == "edit") { if (!(strcmp($row_log['brewYeastType'], "Wine"))) {echo "SELECTED";} }?>>Wine</option>
        <option value="Champagne" <?php if ($action == "edit") { if (!(strcmp($row_log['brewYeastType'], "Champagne"))) {echo "SELECTED";} }?>>Champagne</option>
   </select></td>
</tr>
<tr>
   <td class="dataLabel">Amount:</td>
   <td class="data"><input name="brewYeastAmount" type="text" id="brewYeastAmount" size="30" tooltipText="<?php echo $toolTip_decimal; ?>" value="<?php if ($action == "edit") echo $row_log['brewYeastAmount']; ?>"></td>
   <td class="dataLabel">Starter?</td>
   <td class="data"><input type="radio" name="brewYeastStarter" value="Y" id="brewYeastStarter_0"  <?php if (($action == "edit") && ($row_log['brewYeastStarter'] == "Y")) echo "CHECKED"; ?> /> 
   Yes&nbsp;&nbsp;
   <input type="radio" name="brewYeastStarter" value="N" id="brewYeastStarter_1" <?php if (($action == "edit") && ($row_log['brewYeastStarter'] == "N")) echo "CHECKED"; ?>/> 
   No</td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Yeast Nutrients</h4>
<div class="toggle_container">
<table>
<tr>
   <td class="dataLabel">Type/Amount:</td>
   <td class="data"><input type="text" name="brewYeastNutrients" id="brewYeastNutrients" size="75" value="<?php if ($action == "edit") echo $row_log['brewYeastNutrients']; ?>" /></td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Carbonation</h4>
<div class="toggle_container">
<table>
<tr>
   <td class="dataLabel">Method:</td>
   <td class="data"><input type="radio" name="brewCarbonationMethod" value="Y" id="brewCarbonationMethod_0"  <?php if (($action == "edit") && ($row_log['brewCarbonationMethod'] == "Y")) echo "CHECKED"; ?> /> Forced CO<sub>2</sub>&nbsp;&nbsp;<input type="radio" name="brewCarbonationMethod" value="N" id="brewCarbonationMethod_1" <?php if (($action == "edit") && ($row_log['brewYeastStarter'] == "N")) echo "CHECKED"; ?>/> Bottle Conditioned</td>
</tr>
<tr>
   <td class="dataLabel">Volumes of CO<sub>2</sub>:</td>
   <td class="data"><input name="brewCarbonationVol" type="text" id="brewCarbonationVol" size="10" value="<?php if ($action == "edit") echo $row_log['brewCarbonationVol']; ?>"></td>
</tr>
<tr>
   <td class="dataLabel">Type/Amount:</td>
   <td class="data"><input type="text" name="brewCarbonationNotes" size="75" value="<?php if ($action == "edit") echo $row_log['brewCarbonationNotes']; ?>" /></td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Boil</h4>
<div class="toggle_container">
<table>
<tr>
   <td class="dataLabel">Hours:</td>
   <td class="data"><input name="brewBoilHours" type="text" id="brewBoilHours" size="10" value="<?php if ($action == "edit") echo $row_log['brewBoilHours']; ?>"></td>
</tr>
<tr>
   <td class="dataLabel">Minutes:</td>
   <td class="data"><input name="brewBoilMins" type="text" id="brewBoilMins" size="10" value="<?php if ($action == "edit") echo $row_log['brewBoilMins']; ?>"></td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Fermentation</h4>
<div class="toggle_container">
<table>
<tr>
   <td class="dataLabel">Primary:</td>
   <td class="data"><input type="text" name="brewPrimary" size="5" tooltipText="<?php echo $toolTip_decimal; ?>" value="<?php if ($action == "edit") echo $row_log['brewPrimary']; ?>">&nbsp;days @&nbsp;<input name="brewPrimaryTemp" type="text" id="brewPrimaryTemp" size="5" tooltipText="<?php echo $toolTip_decimal; ?>" value="<?php if ($action == "edit") echo $row_log['brewPrimaryTemp']; ?>">&nbsp;&deg;<?php echo $row_prefs['prefsTemp']; ?></td>
</tr>
<tr>  
   <td class="dataLabel">Secondary:</td>
   <td class="data"><input type="text" name="brewSecondary" size="5" tooltipText="<?php echo $toolTip_decimal; ?>" value="<?php if ($action == "edit") echo $row_log['brewSecondary']; ?>">&nbsp;days @&nbsp;<input name="brewSecondaryTemp" type="text" id="brewSecondaryTemp" size="5" tooltipText="<?php echo $toolTip_decimal; ?>" value="<?php if ($action == "edit") echo $row_log['brewSecondaryTemp']; ?>">&nbsp;&deg;<?php echo $row_prefs['prefsTemp']; ?></td>
</tr>
<tr>
   <td class="dataLabel">Other:</td>
   <td class="data"><input type="text" name="brewOther" size="5" tooltipText="<?php echo $toolTip_decimal; ?>" value="<?php if ($action == "edit") echo $row_log['brewOther']; ?>">&nbsp;days @&nbsp;<input name="brewOtherTemp" type="text" id="brewOtherTemp" size="5" tooltipText="<?php echo $toolTip_decimal; ?>" value="<?php if ($action == "edit") echo $row_log['brewOtherTemp']; ?>">&nbsp;&deg;<?php echo $row_prefs['prefsTemp']; ?></td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Finings</h4>
<div class="toggle_container">
<table>
<tr>
   <td class="dataLabel">Type/Amount:</td>
   <td class="data"><input type="text" name="brewFinings" id="brewFinings" value="<?php if ($action == "edit") echo $row_log['brewFinings']; ?>" /></td>
</tr>
</table>
</div>
<h4 class="trigger"><?php if ($action == "edit") { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/pencil.png"  /></span><?php } else { ?><span class="icon"><img src="<?php echo $base_url; ?>/images/add.png"  /><?php } ?></span>Brewer's Specifics</h4>
<?php } ?>



<p><input type="submit" class="button" value="Submit Entry" alt="Submit Entry" <?php if (($action == "add") && ($remaining_entries == 0)) echo "DISABLED"; ?> /></p>
<input type="hidden" name="brewConfirmed" value="1">
<input type="hidden" name="relocate" value="<?php echo $_SERVER['HTTP_REFERER']; ?>">
</form>

<?php } 
else echo "<div class=\"error\">The requested entry was not entered under the currently logged in user's credentials.</div>";
if ($row_prefs['prefsUseMods'] == "Y") include(INCLUDES.'mods_bottom.inc.php');
?>
</div>
</div>
</div>

