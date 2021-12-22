<?php
/**
 * Plugin Name:       Add WP Genders
 * Plugin URI:        https://github.com/JulioPotier/wp-genders
 * Description:       Handle the basics with this plugin.
 * Version:           1.0
 * Requires at least: 4.7
 * Requires PHP:      7.0
 * Author:            Julio Potier
 * Author URI:        https://secupress.me
 * License:           GPL v2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       genders
 * Domain Path:       /lang
 */

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'genders_settings_link' );
/**
 * Add easy access links to the plugin page
 *
 * @param (array) $links
 * @author Julio Potier
 * @since 1.0
 * @return array
 **/
function genders_settings_link( array $links ): array {
	if ( current_user_can( 'manage_options' ) ) {
		$admin_link   = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php#admin_gender' ), __( 'Admin Gender Setting', 'genders' ) );
		array_push(
			$links,
			$admin_link
		);
	}
	$profile_link = sprintf( '<a href="%s">%s</a>', admin_url( 'profile.php#user_gender' ), __( 'Set Your Preferred Gender', 'genders' ) );
	array_push(
		$links,
		$profile_link
	);
	return $links;
}

add_filter( 'load_textdomain_mofile', 'gender_load_own_i18n', 10, 2 );
/**
 * Load our own i18n to have full control of it
 *
 * @param (string)  $mofile The file to be loaded
 * @param (string)  $domain The desired textdomain
 *
 * @author Julio Potier
 * @since 1.0
 * @return (string) $mofile
 **/
function gender_load_own_i18n( string $mofile, string $domain ): string {
	if ( 'genders' === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
		$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
		if ( ! function_exists( 'determine_locale' ) ) { // WP 5.0.
			$determined_locale     = get_locale();
			if ( is_admin() ) {
				$determined_locale = get_user_locale();
			}
		} else {
			$determined_locale = determine_locale();
		}
		$locale = apply_filters( 'plugin_locale', $determined_locale, $domain );
		$mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/lang/' . $domain . '-' . $locale . '.mo';
	}
	return $mofile;
}

add_action( 'init', 'gender_load_plugin_textdomain_translations' );
/**
 * Load our i18n
 *
 * @author Julio Potier
 * @since 1.0
 * @return void
 **/
function gender_load_plugin_textdomain_translations(): void {
	load_plugin_textdomain( 'genders', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}

add_filter( 'gettext_with_context_default', 'genderize_role_list', 10, 3 );
function genderize_role_list( string $translation, string $text, string $context): string {
	if ( 'User role' !== $context ) {
		return $translation;
	}
	$gendered     = get_roles_gendered();
	$roles_gender = get_roles_gender();
	if ( isset( $gendered[ $text ][ $roles_gender ] ) ) {
		$translation = $gendered[ $text ][ $roles_gender ];
	}
	return $translation;
}

/**
 * Returns the available genders for users.
 *
 * @param (WP_User|int) $user Can be a WP_User object or user_ID (integer)
 * @author Julio Potier
 * @since 1.0
 * @return (array) $genders List of available genders for users as small string, like unique IDs.
 **/
function get_genders( $user = null ): array {
	$main_role = 'administrator';
	if ( ! is_null( $user ) ) {
		if ( is_int( $user ) ) {
			$user      = new WP_User( $user );
		}
		if ( is_a( $user, 'WP_User' ) ) {
			$main_role = reset( $user->roles );
		}
	}
	/**
	 * Add or remove genders for users. "Not Specified" is not filterable since it's WP default.
	 * Please, always add a "%s" where you want to see the gendered role.
	 * @param (array) $genders The users roles, keys are uniq ids
	 * @param (string) $main_role The first role of the user, Default: "Administrator"
	 * @param (array) $user The user or null
	 * @author Julio Potier
	 * @since 1.0
	 */
	$genders = apply_filters( 'genders', 
		[
			'NE' => sprintf( __( 'Neutral / Not Binary / Agender: "%s"', 'genders' ), get_role_gendered( $main_role, 'NE' ) ),
			'M'  => sprintf( __( 'Man: "%s"', 'genders' ), get_role_gendered( $main_role, 'M' ) ),
			'F'  => sprintf( __( 'Woman: "%s"', 'genders' ), get_role_gendered( $main_role, 'F' ) ),
		], $main_role, $user );
	return $genders;
}

/**
 * Returns the available genders for admin responsible persons
 *
 * @author Julio Potier
 * @since 1.0
 * @return (array) $admin_genders List of available genders for admin/s as small string, like unique IDs.
 **/
function get_admin_genders(): array {
	/**
	 * Add or remove genders for admins. "Not Specified" is not filterable since it's WP default.
	 * @param (array) The admin roles, keys are uniq ids
	 * @author Julio Potier
	 * @since 1.0
	 * @return (array) $admin_genders
	 */
	$admin_genders = apply_filters( 'admin_genders',
		[
			'NSP' => __( 'Not Specified (plural): "Contact the administrators…"', 'genders' ),
			'NE'  => __( 'Neutral / Not Binary / Agender: "Contact the administrator…"', 'genders' ),
			'M'   => __( 'Man: "Contact the administrator…"', 'genders' ),
			'F'   => __( 'Woman: "Contact the administrator…"', 'genders' ),
			'MP'  => __( 'Men: "Contact the administrators…"', 'genders' ),
			'FP'  => __( 'Women: "Contact the administrators…"', 'genders' ),
		] );
	return $admin_genders;
}

/**
 * Returns the admin gender setting
 *
 * @author Julio Potier
 * @since 1.0
 * @return (string) Default: WP (Neutral/WP)
 **/
function get_admin_gender(): string {
	$admin_gender = strtoupper( get_option( 'admin_gender' ) );
	return in_array( $admin_gender, array_keys( get_admin_genders() ) ) ? $admin_gender : 'WP';
}

/**
 * Returns the available genders for roles
 *
 * @author Julio Potier
 * @since 1.0
 * @return (array) $roles_genders List of available genders for roles as small string, like unique IDs.
 **/
function get_roles_genders(): array {
	/**
	 * Add or remove genders for roles "Not Specified" is not filterable since it's WP default.
	 * @param (array) The roles, keys are uniq ids
	 * @author Julio Potier
	 * @since 1.0
	 * @return (array) $roles_genders
	 */
	$roles_genders = apply_filters( 'roles_genders',
		[
			'NSP' => __( 'Not Specified (plural): "Administrators"', 'genders' ),
			'NE'  => __( 'Neutral / Not Binary / Agender: "Administrators"', 'genders' ),
			'M'   => __( 'Man: "Administrator"', 'genders' ),
			'F'   => __( 'Woman: "Administrator"', 'genders' ),
			'MP'  => __( 'Men: "Administrators"', 'genders' ),
			'FP'  => __( 'Women: "Administrators"', 'genders' ),
		] );
	return $roles_genders;
}

/**
 * Returns the roles gender setting
 *
 * @author Julio Potier
 * @since 1.0
 * @return (string) Default: WP (Neutral/WP)
 **/
function get_roles_gender(): string {
	$roles_gender = strtoupper( get_option( 'roles_gender' ) );
	return in_array( $roles_gender, array_keys( get_roles_genders() ) ) ? $roles_gender : 'WP';
}


/**
 * Get the translated role for a given gender.
 *
 * @param (string) $role
 * @param (string) $gender
 * @author Julio Potier
 * @since 1.0
 * @return (string) The translated role with the given gender if exist, or the given role is returned.
 **/
function get_role_gendered( string $role, string $gender ): string {
	$role = isset( get_roles_gendered()[ $role ][ $gender ] ) ? get_roles_gendered()[ $role ][ $gender ] : $role;
	return ucfirst( $role );
}

/**
 * Get all translated roles with genders.
 *
 * @author Julio Potier
 * @since 1.0
 * @return (array) 
 **/
function get_roles_gendered(): array {
	$roles['administrator'] = 
					[
						'NSP' => _x( 'Administrators', 'NSP', 'genders' ),
						'NE'  => _x( 'Administrating', 'NE', 'genders' ),
						'M'   => _x( 'Administrator', 'M', 'genders' ),
						'F'   => _x( 'Administrator', 'F', 'genders' ),
						'MP'  => _x( 'Administrators', 'MP', 'genders' ),
						'FP'  => _x( 'Administrators', 'FP', 'genders' ),
					];
	// Also add the en_US i18n role as key, equal to its native key. //// can we avoid that?
	$roles['Administrator'] = $roles['administrator'];

	$roles['author'] = 
					[ 
						'NSP' => _x( 'Authors', 'NSP', 'genders' ),
						'NE'  => _x( 'Writing', 'NE', 'genders' ),
						'M'   => _x( 'Author', 'M', 'genders' ),
						'F'   => _x( 'Author', 'F', 'genders' ),
						'MP'  => _x( 'Authors', 'MP', 'genders' ),
						'FP'  => _x( 'Authors', 'FP', 'genders' ),
					];
	$roles['Author']        = $roles['author'];

	$roles['editor'] = 
					[
						'NSP' => _x( 'Editors', 'NSP', 'genders' ),
						'NE'  => _x( 'Editing', 'NE', 'genders' ),
						'M'   => _x( 'Editor', 'M', 'genders' ),
						'F'   => _x( 'Editor', 'F', 'genders' ),
						'MP'  => _x( 'Editors', 'MP', 'genders' ),
						'FP'  => _x( 'Editors', 'FP', 'genders' ),
					];
	$roles['Editor']        = $roles['editor'];

	$roles['contributor'] = 
					[
						'NSP' => _x( 'Contributors', 'NSP', 'genders' ),
						'NE'  => _x( 'Contributing', 'NE', 'genders' ),
						'M'   => _x( 'Contributor', 'M', 'genders' ),
						'F'   => _x( 'Contributor', 'F', 'genders' ),
						'MP'  => _x( 'Contributors', 'MP', 'genders' ),
						'FP'  => _x( 'Contributors', 'FP', 'genders' ),
					];
	$roles['Contributor']   = $roles['contributor'];

	$roles['subscriber'] = 
					[
						'NSP' => _x( 'Subscribers', 'NSP', 'genders' ),
						'NE'  => _x( 'Subscripting', 'NE', 'genders' ),
						'M'   => _x( 'Subscriber', 'M', 'genders' ),
						'F'   => _x( 'Subscriber', 'F', 'genders' ),
						'MP'  => _x( 'Subscribers', 'MP', 'genders' ),
						'FP'  => _x( 'Subscribers', 'FP', 'genders' ),
					];
	$roles['Subscriber']    = $roles['subscriber'];
	/**
	 * Filter the gendered roles to add the ones from your plugin
	 * 
	 * @param (array) Every roles, localised with gender.
	 * @author Julio Potier
	 * @since 1.0
	 */
	return apply_filters( 'roles_gendered', $roles );
}

/**
 * Return the user gender, N (Neutral/WP) by default
 *
 * @param (int|WP_User) $user_ID The user ID or object
 * @author Julio Potier
 * @since 1.0 
 * @return (string) The user's gender
 **/
function get_user_gender( $user_ID ): string {
	if ( is_a( $user_ID, 'WP_User' ) ) {
		$user_ID = $user_ID->ID;
	}
	$user_ID = (int) $user_ID;
	$gender  = get_user_meta( $user_ID, '_gender', true );
	$gender  = $gender ?: 'WP';
	$gender  = isset( get_genders()[ $gender ] ) ? $gender : 'WP';

	return $gender;
}

add_action( 'personal_options', 'field_user_gender_radio' );
/**
 * Display some radios input to select a gender for a user (profile page)
 *
 * @param (WP_User) $profileuser
 * @author Julio Potier 
 * @since 1.0
 * @return void
 **/
function field_user_gender_radio( WP_User $profileuser ): void {
	$user_gender = get_user_gender( $profileuser->ID );
?>
<table class="form-table" role="presentation">
	<tbody>
	<tr class="user-rich-editing-wrap">
		<th scope="row"><?php _e( 'Preferred gender', 'genders' ); ?></th>
		<td>
			<fieldset id="user_gender">
				<label><input type="radio" name="gender" value="WP" <?php checked( 'WP' === $user_gender || ! $user_gender ); ?> ><?php printf( __( 'Not Specified: "%s"', 'genders' ), _x( 'Administrator', 'copy/paste the one from "User role" context from your lang', 'genders' ) ); ?>*</label><br>
			<?php foreach( get_genders() as $gid => $_gender ) { ?>
				<label><input type="radio" name="gender" value="<?php echo esc_attr( $gid ); ?>"  <?php checked( $user_gender, $gid ); ?> ><?php echo esc_html( $_gender ); ?></label><br>
			<?php } ?>
			</select>
			<p class="description">* <?php _e( 'WordPress Native Translations.', 'genders' ); ?></p>
		</td>
	</tr>
	</tbody>
</table>
<?php
}

add_action( 'personal_options_update', 'save_gender_setting' );
add_action( 'edit_user_profile_update', 'save_gender_setting' );
/**
 * Update (or delete) the "_gender" user meta on profile/user edit save.
 *
 * @param (int) $user_ID
 * @author Julio Potier 
 * @since 1.0
 * @return void
 **/
function save_gender_setting( int $user_ID ): void {
	$value = isset( $_POST['gender'] ) ? sanitize_gender( $_POST['gender'] ) : 'WP';
	$value = isset( get_genders()[ $value ] ) ? $value : 'WP';
	if ( 'WP' === $value || ! $value ) { // We don't store the default WP behavior.
		delete_user_meta( $user_ID, '_gender' );
		return;
	}
	update_user_meta( $user_ID, '_gender', $value );
}


add_filter( 'get_role_list', 'get_role_list_gendered', 10000, 2 );
/**
 * Get role list for a given user with gendered translations
 *
 * @param (array) $role_list The roles list from WP
 * @param (WP_User) $user_object
 * @author Julio Potier
 * @since 1.0
 * @return (array)
 **/
function get_role_list_gendered( array $role_list, WP_User $user_object ): array {
	$user_gender    = get_user_gender( $user_object );
	$roles_gendered = get_roles_gendered();
	//// translate user role en ?
	foreach ( $role_list as $en_role => &$i18n_role ) {
		if ( isset( $roles_gendered[ $en_role ][ $user_gender ] ) ) {
			$i18n_role = $roles_gendered[ $en_role ][ $user_gender ];
		}
	}
	return $role_list;
}

/**
 * Sanitize thengender ID
 *
 * @param (string) $str A gender ID to be sanitized
 * @author Julio Potier
 * @since 1.0
 * @return (string) The gender ID, uppercased
 **/
function sanitize_gender( string $str ): string {
	return strtoupper( sanitize_key( $str ) );
}

add_action( 'admin_init', 'register_settings_admin_gender' );
/**
 * Register our setting in the general > default WP panel (wp-admin/options-general.php page)
 *
 * @author Julio Potier
 * @since 1.0
 * @return void
 **/
function register_settings_admin_gender(): void {
	register_setting( 
		'general', 
		'admin_gender',
		'sanitize_gender'
	);
	add_settings_field( 
		'admin_gender', 
		__( 'Administrator(s) Gender(s)', 'genders' ), 
		'field_admin_gender', 
		'general', 
		'default' 
	);
	register_setting( 
		'general', 
		'roles_gender',
		'sanitize_gender'
	);
	add_settings_field( 
		'role_list_gender', 
		__( 'Role List Gender', 'genders' ), 
		'field_roles_gender', 
		'general', 
		'default' 
	);
}

/**
 * Display a field in general WP settings for admin gender
 *
 * @see register_settings_admin_gender()
 * @author Julio Potier
 * @since 1.0 
 * @return void
 **/
function field_admin_gender(): void {
	$admin_genders = get_admin_genders();
	$admin_gender  = get_admin_gender();
	?>
	<fieldset id="admin_gender">
		<label><input type="radio" name="admin_gender" value="N"  <?php checked( $admin_gender, 'WP' ); ?> ><?php _e( 'Not Specified: "Contact the administrator…"', 'genders' ); ?> *</label><br>
		<?php foreach ( $admin_genders as $gid => $_gender ) { ?>
		<label><input type="radio" name="admin_gender" value="<?php echo esc_attr( $gid ); ?>" <?php checked( $admin_gender, $gid ); ?>><?php echo esc_html( $_gender ); ?></label><br>
		<?php } ?>
	</fieldset>
	<p class="description">* <?php _e( 'WordPress Native Translations.', 'genders' ); ?></p>
	<?php
}

/**
 * Display a field in general WP settings for roles gender
 *
 * @see register_settings_roles_gender()
 * @author Julio Potier
 * @since 1.0 
 * @return void
 **/
function field_roles_gender(): void {
	$roles_genders = get_roles_genders();
	$roles_gender  = get_roles_gender();

	echo '<strong>' . __( 'How the roles should be displayed when not associated with a user', 'genders' ) . '</strong>';
	?>
	<fieldset id="roles_gender">
		<label><input type="radio" name="roles_gender" value="WP"  <?php checked( $roles_gender, 'WP' ); ?> ><?php printf( __( 'Not Specified: "%s"', 'genders' ), _x( 'Administrator', 'copy/paste the one from "User role" context from your lang', 'genders' ) ); ?> *</label><br>
		<?php foreach ( $roles_genders as $gid => $_gender ) { ?>
		<label><input type="radio" name="roles_gender" value="<?php echo esc_attr( $gid ); ?>" <?php checked( $roles_gender, $gid ); ?>><?php echo esc_html( $_gender ); ?></label><br>
		<?php } ?>
	</fieldset>
	<p class="description">* <?php _e( 'WordPress Native Translations.', 'genders' ); ?></p>
	<?php
}

add_filter( 'gettext', 'filter_administrator_gender', 10000, 3 );
/**
 * Filter the 'administrator' piece in po/mo sentences and fix it with gendered translation.
 *
 * @see add_gendered_translation() 
 * @param (string) $translation Translated string from po/mo files
 * @param (string) $en Original string from core (WP, Plugins, Themes)
 * @param (string) $domain
 * @param (string) $gender
 * @author Julio Potier
 * @since 1.0 
 * @return (string) The localy translated new gendered piece of sentence if exists
 **/
function filter_administrator_gender( string $translation, string $en, string $domain, string $gender = '' ): string {
	static $file_data;
	// Do not change our translations.
	if ( 'genders' === $domain ) {
		return $translation;
	}
	$gender = ! $gender ? get_admin_gender() : $gender;
	if ( ! isset( $file_data ) ) {
		$file_data = get_gendered_content_from_data();
	}
	/**
	 * Filter the content, mainly to add your content, see add_gendered_translation()
	 *  
	 * @see add_gendered_translation() 
	 * @see get_gendered_content_from_data() 
	 * @param (array) The strings from json data
	 * @param (string) $domain
	 * @param (string) $gender
	 * @author Julio Potier
	 * @since 1.0
	 */
	$contents = apply_filters( 'filter_administrator_gender', $file_data, $domain, $gender );
	// var_dump( $contents );
	// die();
	if ( empty( $contents['strings'] ) ) {
		return $translation;
	}
	foreach( $contents['strings'] as $_content => $_index ) {
		$_content = str_replace( [ '[', ']', '?' ], [ '&§&', '@§@', '°§°' ], $_content );
		$_content = preg_quote( $_content );
		$_content = str_replace( [ '&§&', '@§@', '°§°' ], [ '[', ']', '?' ], $_content );
		$_index   = is_array( $_index ) ? reset( $_index ) : $_index;
		// var_dump( $_content . ' ' . $_index );
		//// ONE BUG PREG WITH ALL CHAINS??
		if ( ! preg_match( '/(.*)' . $_content . '(.*)/ui', $translation, $matches ) || ! isset( $contents['i18n'][ $_index ][ $gender ] ) ) {
			continue;
		}
		$val = $contents['i18n'][ $_index ][ $gender ];
		// Try to keep the uppercase for first letter
		if ( ctype_upper( $matches[0][0] ) && 0 === strcmp( strtolower( $matches[0][0] ), $val[0] ) ) {
			$val = ucfirst( $val );
		}
		$translation  = preg_replace( '/(.*)' . $_content . '(.*)/ui', '$1' . $val . '$2', $translation );
	}
	return $translation;
}

/**
 * Load json data from files, contribute on github to add yours
 * https://github.com/JulioPotier/wp-genders
 *
 * @author Juio Potier
 * @since 1.0
 * @return (array)
 **/
function get_gendered_content_from_data(): array {
	static $content;
	if ( ! is_null( $content ) ) {
		return $content;
	}
	// At least those 3 locales:
	// en_US: For any non translated strings that could need genderization
	// get_locale : Front-end
	// get_user_locale : Back-end
	$locales = array_filter( array_flip( array_flip( [ 'en_US', get_locale(), get_user_locale() ] ) ) );
	$content = []; 
	foreach ( $locales as $key ) {
		// fr_FR (more accurate)
		$file    = sprintf( '%s/data/%s.json', __DIR__, $key );
		if ( is_readable( $file ) ) {
			$content = array_merge_recursive( $content, json_decode( file_get_contents( $file ), true ) );
		}
		// fr 
		$key  = substr( $key, 0, 2 );
		$file     = sprintf( '%s/data/%s.json', __DIR__, $key );
		if ( is_readable( $file ) ) {
			$content = array_merge_recursive( $content, json_decode( file_get_contents( $file ), true ) );
		}
	}
	return $content;
}

/**
 * Add a custom gendered translation on your site in your language.
 * !!! Do not use i18n functions here !!!
 * Read the plugin FAQ to get a snippet as example
 *
 * @param (string) The piece of sentence you want to change
 * @param (array) Each gendered translation for each available gender
 * @param (string) $priority Default: 'low'; 'low/whatever' = added to the bottom (last) & 'high' added to the top (first)
 * @author Julio Potier
 * @since 1.0
 * @return void
 **/
function add_gendered_translation( string $text, array $translations, string $priority = 'low' ): void {
	add_filter( 'filter_administrator_gender',
		function( $content ) use ( $text, $translations, $priority ): array {
			$uniqid      = uniqid();
			$new_content = [ 'i18n' => [ $uniqid => $translations ], 'strings' => [ $text => $uniqid ] ];
			if ( 'high' === $priority ) {
				$content = array_merge_recursive( $new_content, $content );
			} else {
				$content = array_merge_recursive( $content, $new_content );
			}
			return $content;
		}
	);
}

/**
 * Will change the gender of desired text, will NOT use WP i18n, just our system
 *
 * @param (string) $text Text to be translated
 * @param (string) $gender The specified gender
 * @param (string) $domain Default: 'default'
 * @author Julio Potier
 * @since 1.0
 * @return (string)
 **/
function _g( string $text, string $gender = '', string $domain = 'default' ): string {
	return filter_administrator_gender( $text, '', $domain, $gender );
}

/**
 * Will change the gender of desired text, will NOT use WP i18n, just our system, and will echo it
 *
 * @see _g()
 * @param (string) $text Text to be translated
 * @param (string) $gender The specified gender
 * @param (string) $domain Default: 'default'
 * @author Julio Potier
 * @since 1.0
 * @return void
 **/
function _ge( string $text, string $gender = '', string $domain = 'default' ): void {
	echo _g( $text, $gender, $domain );
}
