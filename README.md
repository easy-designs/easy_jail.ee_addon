Easy JAIL for ExpressionEngine
==============================

ExpressionEngine Plugin to automate use of the jQuery Asynchronous Image Loader

The API
-------

First off, this plugin requires jQuery. Using it requires 2 steps:

**Step 1:** Wrap the markup you want to JAIL in `{exp:easy_jail:prep}`

	{exp:easy_jail:prep}
		{body}
	{/exp:easy_jail:prep}

This will cause the plugin to convert

	<img src="foo.png" alt=""/>

into

	<img class="jail" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="foo.png" alt=""/>
	<noscript><img src="foo.png" alt=""/></noscript>

Providing it with additional params allows you to customize certain bits:

* `xhtml="n"` - HTML output
* `blank_img="my_blank.gif"` - Your custom blank image
* `class_name="custom_class"` - Your custom class choice

**Step 2:** Include `{exp:easy_jail:js}` at the end of your `body` element, after you included jQuery.

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="/j/jquery.js"><\/script>')</script>
	{exp:easy_jail:js}

By default, this will include the JAIL script and a baseline configuration. To configure the output of the script, you can use the following parameters:

* `class_name="custom_class"` - Your custom class choice
* `config="{offset:300}"` - Your custom configuration (see the [JAIL documentation](http://sebarmeli.github.io/JAIL/) for a run-down of options)


License
-------

Easy JAIL for ExpressionEngine and jQuery JAIL are both distributed under the liberal MIT License.