<?php
/*
=====================================================
 Easy JAIL - by Easy Designs, LLC
-----------------------------------------------------
 http://easy-designs.net/
=====================================================
 This extension was created by Aaron Gustafson
 (aaron@easy-designs.net) with contributions from
 Jens Korff
 This work is licensed under the MIT License.
=====================================================
 File: pi.easy_jail.php
-----------------------------------------------------
 Purpose: Automates the implementation of Sebastiano 
 Armeli-Battana’s jQuery Asynchronous Image Loader 
 Plugin
=====================================================
*/

$plugin_info = array(
	'pi_name'			=> 'Easy JAIL',
	'pi_version'		=> '1.0',
	'pi_author'			=> 'Aaron Gustafson',
	'pi_author_url'		=> 'http://easy-designs.net/',
	'pi_description'	=> 'Automates the implementation of Sebastiano Armeli-Battana’s jQuery Asynchronous Image Loader Plugin',
	'pi_usage'			=> Easy_jail::usage()
);

class Easy_jail {

	var $return_data;
	var $template = '<img class="{class_name}" src="{blank_img}" data-src="{real_img}" {attributes}/><noscript><img src="{real_img}" {attributes}/></noscript>';
	var $xhtml = TRUE;
	var $class_name = 'jail';
	var $blank_img = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
	var $alt = '';
	var $config = '';

	/**
	 * Easy_jail constructor
	 * sets any overrides and triggers the processing
	 * 
	 * @param str $str - the content to be parsed
	 */
	function __construct()
	{
		$this->return_data = ee()->TMPL->tagdata;
	} # end Easy_jail constructor
  
	/**
	 * Easy_jail::prep()
	 * processes the supplied content based on the configuration
	 * 
	 * @param str $str - the content to be parsed
	 */
	function prep( $str='', $xhtml='', $class_name='', $blank_img='' )
	{
		# get any tag overrides
		if ( empty( $xhtml ) )
		{
			$xhtml = ee()->TMPL->fetch_param( 'xhtml', $this->xhtml );
		}
		if ( empty( $class_name ) )
		{
			$class_name = ee()->TMPL->fetch_param( 'class_name', $this->class_name );
		}
		if ( empty( $blank_img ) )
		{
			$blank_img = ee()->TMPL->fetch_param( 'blank_img', ( empty( $blank_img ) ? $this->blank_img : $blank_img ) );
		}
		
		# Fetch string
		if ( empty( $str ) )
		{
			$str = ee()->TMPL->tagdata;
		}

		# trim
		$str = trim( $str );

		$lookup = '/(<img([^>]*)\/?>)/';
		if ( preg_match_all( $lookup, $str, $found, PREG_SET_ORDER ) )
		{
			# loop the matches
			foreach ( $found as $instance )
			{
				$o_img = $instance[1];
				$src = '';
				
				# get the attributes
				$attributes	= array();
				
				# remove the /
				if ( substr( $instance[2], -1, 1 ) == '/' )
				{
					$instance[2] = substr( $instance[2], 0, -1 );
				} 

				# Get all attributes
				# Reference: http://stackoverflow.com/questions/138313/how-to-extract-img-src-title-and-alt-from-html-using-php#answer-2937682
				$doc = new DOMDocument();
				@$doc->loadHTML($o_img);
				$tags = $doc->getElementsByTagName('img');
				
				foreach ( $tags as $tag )
				{
					foreach ( $tag->attributes as $attribute )
					{
						$name = $attribute->name;
						$value = $attribute->value;
				
						if ( $name == 'src' )
						{
							$src = $value;
						} else {
							$attributes[$name] = $name . '="' . $value . '"';
						}
					}
				}
				
				# enforce an alt attribute
				if ( ! isset( $attributes['alt'] ) )
				{
					$attributes['alt'] = $this->alt;
				}
				
				# build the new image
				$swap = array(
					'attributes'	=> implode( ' ', $attributes ),
					'class_name'	=> $class_name,
					'blank_img'		=> $blank_img,
					'real_img'		=> $src
				);
				$n_img = ee()->functions->var_swap( $this->template, $swap );
				
				# XHTML?
				if ( ! $xhtml )
				{
					$n_img = str_replace( '/>', '>', $n_img );
				}
				
				$str = str_replace( $o_img, $n_img, $str );
				
			} # end foreach instance
			
		} # end if match
		
		$this->return_data = $str;
		
		return $this->return_data;
		
	} # end Easy_jail::prep()

	/**
	 * Easy_jail::js()
	 * Describes how the plugin is used
	 */
	function js( $class_name='', $config='' )
	{
		$js = '';
		
		# get tag params
		if ( empty( $class_name ) )
		{
			$class_name = ee()->TMPL->fetch_param( 'class_name', $this->class_name );
		}
		if ( empty( $config ) )
		{
			$config = ee()->TMPL->fetch_param( 'config', $this->config );
		}
		
		# get JAIL
		$js .= file_get_contents( PATH_THIRD . '/easy_jail/vendors/jail/dist/jail.min.js' ) . "\n\n";
		
		# build the trigger
		$template = '(function(window,$){$("img.{class_name}").jail({jail_config});$(window).on("load",function(){$(this).resize();});}(this,jQuery));';
		$swap = array(
			'class_name'	=> $class_name,
			'jail_config'	=> $config
		);
		$js .= ee()->functions->var_swap( $template, $swap );
		
		$this->return_data = '<script>' . $js . '</script>';
		
		return $this->return_data;
		
	} # end Easy_jail::js()

	/**
	 * Easy_jail::usage()
	 * Describes how the plugin is used
	 */
	function usage()
	{
		ob_start(); ?>
First off, this plugin requires jQuery. Using it requires 2 steps:

1) Wrap the markup you want to JAIL in {exp:easy_jail:prep}

{exp:easy_jail:prep}
	{body}
{/exp:easy_jail:prep}

This will cause the plugin to convert

<img src="foo.png" alt=""/>

into

<img class="jail" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="foo.png" alt=""/>
<noscript><img src="foo.png" alt=""/></noscript>

Providing it with additional params allows you to customize certain bits:

* xhtml="n" - HTML output
* blank_img="my_blank.gif" - Your custom blank image
* class_name="custom_class" - Your custom class choice

2) Include {exp:easy_jail:js} at the end of your body element, after you included jQuery.

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="/j/jquery.js"><\/script>')</script>
{exp:easy_jail:js}

By default, this will include the JAIL script and a baseline configuration. To configure the output of the script, you can use the following parameters:

* class_name="custom_class" - Your custom class choice
* config="{offset:300}" - Your custom configuration (see http://sebarmeli.github.io/JAIL/ for a run-down of options)
<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	} # end easy_jail::usage()

} # end Easy_jail

/* End of file pi.easy_jail.php */ 
/* Location: ./system/expressionengine/third_party/easy_jail/pi.easy_jail.php */