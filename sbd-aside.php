<?php
/*
Plugin Name: SBD Aside
Plugin URI: http://seanbdurkin.id.au/pascaliburnus2/archives/51
Description: Plugin inserts "aside" content
Author: Sean B. Durkin
Version: 0.03
Author URI: http://www.seanbdurkin.id.au
*/

add_action('admin_init', 'sbd_init_options' );
add_action('admin_menu', 'sbd_add_option_page');
add_action('wp_enqueue_scripts'   , 'inject_sbd_aside_regular_scripts');
add_action('admin_enqueue_scripts', 'inject_sbd_aside_admin_scripts'  );
wp_register_script( 'sbd_aside_script'    , plugins_url() . '/sbd-aside/sbd-aside.js');
wp_register_style ( 'sbd_aside_stylesheet', plugins_url() . '/sbd-aside/sbd-aside.css');
if ( !is_admin() ){
  add_filter('the_content', 'handle_sbd_aside');
}

function inject_sbd_aside_regular_scripts() {
   wp_enqueue_style ( 'sbd_aside_stylesheet');
   wp_enqueue_script( 'sbd_aside_script'    );
}

function inject_sbd_aside_admin_scripts() {
   wp_enqueue_style ( 'sbd_aside_stylesheet');
}

function sbd_init_options(){
	register_setting( 'sbd_aside_options', 'sbd_options_store');
}

function sbd_add_option_page() {
	add_options_page('SBD Aside Options', 'SBD Aside', 'manage_options', 'sbd_options_storeoptions', 'make_sbd_aside_options_page');
}

function make_sbd_aside_options_page() {
	?>
	<div class="wrap">
	  <h2>SBD Aside Options</h2>
	  <form id="sbd-alert-options-form" method="post" action="options.php" class="sbd-floatbox">
          <?php settings_fields('sbd_aside_options'); ?>
	  <?php $options = get_option('sbd_options_store'); ?>
          <fieldset class="sbd-fieldset">
            <legend>Positioning</legend>
	    <fieldset class="sbd-radio sbd-first-field">
             <legend>Side-bar alignment</legend>
              <label for="alignment-left">
                <input id="alignment-left" name="sbd_options_store[aside_alignment]" type="radio" value="left" 
                <?php checked('left', $options['aside_alignment']); ?> />Left
              </label>
			  
              <label for="alignment-right">
                <input id="alignment-right" name="sbd_options_store[aside_alignment]" type="radio" value="right" 
                <?php checked('right', $options['aside_alignment']); ?>
		<?php checked(''     , $options['aside_alignment']); ?> />Right
              </label>
		</fieldset>
		
	     <fieldset class="sbd-radio">
             <legend>Initial size</legend>
              <label for="collapsed">
                <input id="collapsed" name="sbd_options_store[aside_collapse_state]" type="radio" value="collapsed" 
                <?php checked('collapsed', $options['aside_collapse_state']); ?>
		<?php checked(''         , $options['aside_collapse_state']); ?> />Collapsed
              </label>
			  
              <label for="expanded">
                <input id="expanded" name="sbd_options_store[aside_collapse_state]" type="radio" value="expanded" 
                <?php checked('expanded' , $options['aside_collapse_state']); ?> />Expanded
              </label>
            </fieldset>

            <label for="width">Width
	     <input id="width" name="sbd_options_store[aside_width]" type="text" placeholder="Enter width here"
               value="<?php
                        $w = $options['aside_width'];
                        if ($w=='') {$w = '200px';}
                        echo $w
                      ?>" />
	    </label>

          </fieldset>             

          <fieldset class="sbd-fieldset">
            <legend>Styling</legend>
	    <fieldset class="sbd-radio sbd-first-field">
             <legend>Corners</legend>
              <label for="round">
                <input id="round" name="sbd_options_store[aside_corners]" type="radio" value="round" 
                <?php checked('round', $options['aside_corners']); ?>
                <?php checked(''     , $options['aside_corners']); ?> />Round
              </label>
			  
              <label for="sharp">
                <input id="sharp" name="sbd_options_store[aside_corners]" type="radio" value="sharp" 
                <?php checked('sharp', $options['aside_corners']); ?> />Sharp cut
              </label>
	    </fieldset>

	    <fieldset class="sbd-radio">
             <legend>Under caption rule</legend>
              <label for="rule-style-norm">
                <input id="rule-style-norm" name="sbd_options_store[aside_hr_style]" type="radio" value="sbd-regular" 
                <?php checked('sbd-regular', $options['aside_hr_style']); ?> />Regular
                <hr class="sbd-regular" />
              </label>
			  
              <label for="rule-style-norm-swish">
                <input id="rule-style-norm-swish" name="sbd_options_store[aside_hr_style]" type="radio" value="sbd-swish" 
                <?php checked('sbd-swish', $options['aside_hr_style']); ?>
                <?php checked(''         , $options['aside_hr_style']); ?> />Swish
                <hr class="sbd-swish" />
              </label>
	    </fieldset>


            <label for="bg-colour">Box background colour
	     <input id="bg-colour" name="sbd_options_store[aside_bg_colour]" type="text" placeholder="Enter colour here"
               value="<?php
                        $w = $options['aside_bg_colour'];
                        if ($w=='') {$w = '#9ae6d4';}
                        echo $w
                      ?>" />
	    </label>

	    </fieldset>

           <p class="submit sbd-submit">
             <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
            </p>
 	 </form>
	</div>
	<?php	
}


function generate_random_str( $length=10)
{
  return substr(md5(rand()), 0, $length);
}

function generate_place_marker()
{
  return '@' . generate_random_str( 10) . '@';
}

function GetAsideProperty( $aside_instruction, $prop_name) {
  preg_match( '~^(<p>)?  '.$prop_name.'\s*=\s*(.*)(<br />|</p>)$~mi', $aside_instruction, $matches);
  $value = $matches[2];
  if ($value=='') {
    $options_array = get_option('sbd_options_store');
    $value = $options_array['aside_'.$prop_name];
	}
  return $value;
}

function GetCaption( $aside_instruction) {
  return GetAsideProperty( $aside_instruction, 'caption');
}

function GetAlignment( $aside_instruction) {
  $ret = GetAsideProperty( $aside_instruction, 'alignment');
  if ($ret == '') { $ret = 'right';}
  return $ret;
}

function GetDoIncludeButton( $aside_instruction) {
  $ret = GetAsideProperty( $aside_instruction, 'collapse_state');
  if ($ret == '') { $ret = 'collapsed'; }
  return $ret == 'collapsed';
}

function GetCornerClass( $aside_instruction) {
  $ret = GetAsideProperty( $aside_instruction, 'corners');
  if ($ret == '') { $ret = 'round'; }
  return 'sbd-'.$ret;
}

function GetRuleClass( $aside_instruction) {
  $ret = GetAsideProperty( $aside_instruction, 'hr_style');
  if ($ret == '') { $ret = 'sbd-swish'; }
  return $ret;
}

function GetAsideClass( $aside_instruction) {   
  $align = GetAlignment( $aside_instruction);
  if (($align == 'right') || ($align == ''))
      { return 'sbd-aside sbd-right';}
    else	
      { return 'sbd-aside sbd-left';}
}

function GetBGColour( $aside_instruction) {
  $ret = GetAsideProperty( $aside_instruction, 'bg_colour');
  if ($ret == '') { $ret = '#9ae6d4'; }
  return $ret;
}


function GetBody( $aside_instruction) { 
  return preg_replace( '~^((<p>)?  \S+\s*=\s*.*?(<br \/>|<\/p>)\n?)*~mi', '', $aside_instruction);
}

function GetWidth( $aside_instruction) {
  $ret = GetAsideProperty( $aside_instruction, 'width');
  if ($ret=='') {$ret = '200px';}
  return $ret;
}


function handle_sbd_aside($the_content)
{
  $begin = generate_place_marker();
  $end   = generate_place_marker();

  $new_content = preg_replace(
    '~^((<p>)?\[aside\](<br />|</p>))(.*?)(^(<p>)?\[\/aside\](<br />|</p>))~ms',
    $begin . '$4' . $end,
    $the_content);

  $new_content = preg_replace_callback(
    '~^(<p>)?(!+\[\/?aside\])~m',
    function ($match) {
      return $match[1] . substr( $match[2], 1);
      },
    $new_content);  

  $pattern = '~'.$begin.'(.*?)'.$end.'~s';

  return preg_replace_callback(
    $pattern,
    function ($match) {
      $aside_instruction = $match[1];
      $caption = GetCaption( $aside_instruction);
      $doIncludeButton = GetDoIncludeButton( $aside_instruction);
      $rule_class =  GetRuleClass( $aside_instruction);
      $aside_class = GetAsideClass( $aside_instruction) . ' ' . GetCornerClass( $aside_instruction);  
      $body = GetBody( $aside_instruction);
      $width = GetWidth( $aside_instruction);
      $bg_colour = GetBGColour( $aside_instruction);

      $aside = '<aside ';
      if ($width != '') {$aside .= 'style="width:' . $width . '; background-color:'.$bg_colour.';" ';}
      $aside .= 'class="'.$aside_class.'"><header class="sbd-header">';
      if ($doIncludeButton) {
        $aside .= '<img src="' . plugins_url() . '/sbd-aside/expand.gif"' . <<<HEREDOC
 width="13" height="14" border="0"
 alt="Show/Hide" title="Show/Hide"
 onclick="togglePannel(this)"/>
HEREDOC;
        }
      $aside .= $caption;
      $aside .= '</header>'
                . ' <div class="';
      if ($doIncludeButton) {$aside .= 'sbd-hidden ';}
      $aside .= 'sbd-tight">'
                . '<hr class="' . $rule_class . '"/><span class="sbd-content">'
                . $body
                . '</span></div></aside>';
      return $aside;
      },
    $new_content);

}

?>