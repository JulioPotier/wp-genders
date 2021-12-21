
# wp-genders
Add genders to role list, admin responsible and change the global translations all over your WordPress site.

## How to use
You have nothing to do but activate the plugin. Life is beautiful :)

## How to add a language
Thanks to contribute to the github repository https://github.com/JulioPotier/wp-genders and add your strings.
The "*model.php*" file is a simple way to get the JSON data we need. once filled and filename change, open this file in any website and you'll get a *lang_LANG.json* file, open a pull request!
Also, translate the plugin as usual to create "gender-lang_LANG.mo/mo" files.

## How to add a custom string in my plugin
Just use the __(), _e(), etc like usual, all is automated. To get a better fit, please use the same english words as WordPress is using.

## How to add my custom replacement strings in my plugin
You can use this snippet:
<pre>
add_action( 'plugins_loaded', function() {
   if ( function_exists( 'add_gendered_translation' ) ) {
	    add_gendered_translation( 'Hey my man or woman', // sentence to translate in your plugin with __() as usual
			[ // gendered translation in your language. Repeat for each needed language.
				'NSP' => 'Hey everyone',
				'NE' => 'Hey you',
				'M'  => 'Hey my boy',
				'F'  => 'Hey my lady',
				'MP' => 'Hey my boys',
				'FP' => 'Hey my ladies',
			]
	    );
    }
}</pre>
If you try to override an existing i18n, you'll need a higher priority, but remember that only the most prior i18n will be used.
French demo to replace *"L’administrateur ou l’administratrice"* by *"le Président"* ou *"la Présidente"*.
<pre>
add_action( 'init', function() {
	add_gendered_translation( "[Ll][’']administrateur ou l[’']administratrice",
			[
				'M'  => 'le Président', // always start with a lower case, we will handle the upper if needed.
				'F'  => 'la Présidente',
			], 'high' ); // "high" priority here
	}
);
</pre>
You may need to repeat this operation for every sentence from the fr_FR.json file here, or only this case will be covered.

**Legend:**

NSP : Not Specified Plural (Mimic the WP behavior with plural form)

NE : Neutral / Non-Binary / Agender

M : Male

F : Female

MP : Male Plural aka Men

FP : Female Plural aka Women

By default we use "WP" for WordPress native translations. (you can't change it, even when adding a "WP" key, forget it)
## How to add a gendered role from my plugin
You can use this snippet (French for demo):
<pre>
add_filter( 'roles_gendered', function( $roles ) {
	$roles['tutor_instructor'] =
	    [ // "tutor_instructor" key from LMS plugin
			'NE' => 'Responsable de l’instruction',
			'M' => 'Tuteur instructeur',
			'F' => 'Tutrice instructrice',
		];
	$roles['Tutor Instructor'] = $roles['tutor_instructor']; // "Tutor Instructor" english label from LMS plugin
	
	return $roles;
});</pre>
## How to use your _g() and _ge() functions
Do not use them in your plugin, only for testing purposes, we do not need another i18n function in the core, overcharge __() can be enough and automatically retrocompatible.
__g() will return the string and _ge() will print it.
So, to test a translation, do this (French for demo):
<pre>
_ge( 'Ce site est géré par un administrateur ou une administratrice' );
</pre>
This will be changed regarding the admin gender setting (example: Male Plural):
<pre>"Ce site est géré par des administrateurs."</pre>
If you want to force a gender:
<pre><?php
_ge( 'Ce site est géré par un administrateur ou une administratrice', 'F' );
"Ce site est géré par une administratrice."</pre>


## What if my language do not need a specific gender because of its native grammar
You still may need this plugin if a user is using a different locale than you or the website.