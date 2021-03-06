<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.


/**
 * User Edit Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );


# 如果是客户则不能访问该页面
if(isset($_SESSION['is_out_user'])) {
//    print_header_redirect( 'view_all_bug_page.php' );
    print_error_page(error_string( ERROR_ACCESS_DENIED ));
}


auth_reauthenticate();

access_ensure_global_level( config_get( 'manage_user_threshold' ) );

$t_user_id = gpc_get_int( 'user_id' );
if( $t_user_id === 0 ) {
    error_parameters( $f_username );
    trigger_error( ERROR_USER_BY_NAME_NOT_FOUND, ERROR );
}

$user_type = 1;

$t_query = 'SELECT * FROM {out_user} WHERE id = '.db_param();
$t_result = db_query( $t_query, [$t_user_id] );
$t_user = db_fetch_array($t_result);

function check_checked_user_type($user_type,$type){
     if($user_type==$type){
       return 'checked=checked';
     }
}
$t_project_id = $t_user['project_id'];
# Ensure that the account to be updated is of equal or lower access to the
# current user.
access_ensure_global_level( $t_user['access_level'] );

$t_ldap = ( LDAP == config_get_global( 'login_method' ) );

layout_page_header();

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_out_user_page.php' );
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

<!-- USER INFO -->
<div id="edit-user-div" class="form-container">
	<form id="edit-user-form" method="post" action="manage_out_user_update.php">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-user"></i>
					<?php echo lang_get('edit_out_user_title') ?>
				</h4>
			</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="form-container">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php echo form_security_field( 'manage_user_update' ) ?>
			<!-- Title -->
			<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />

			<!-- Username -->
			<tr>
				<td class="category">
					<?php echo lang_get( 'username_label' ) ?>
				</td>
				<td>
					<input id="edit-username" type="text" class="input-sm" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" name="username" value="<?php echo string_attribute( $t_user['username'] ) ?>" />
				</td>
			</tr>

			<!-- Realname -->
			<tr><?php
			if( $t_ldap && ON == config_get( 'use_ldap_realname' ) ) {
				# With LDAP
				echo '<td class="category">' . lang_get( 'realname_label' ) . '</td>';
				echo '<td>';
				echo string_display_line(  $t_user['realname'] );
				echo '</td>';
			} else {
				# Without LDAP ?>
				<td class="category"><?php echo lang_get( 'realname_label' ) ?></td>
				<td><input id="edit-realname" type="text" class="input-sm" size="32" maxlength="<?php echo DB_FIELD_SIZE_REALNAME;?>" name="realname" value="<?php echo string_attribute( $t_user['realname'] ) ?>" /></td><?php
			}
		?>
			</tr>
			<!-- Email -->
			<tr><?php
			if( $t_ldap && ON == config_get( 'use_ldap_email' ) ) {
				# With LDAP
				echo '<td class="category">' . lang_get( 'email_label' ) . '</td>';
				echo '<td>' . string_display_line( $t_user['email'] ) . '</td>';
			} else {
				# Without LDAP
				echo '<td class="category">' . lang_get( 'email_label' ) . '</td>';
				echo '<td>';
				print_email_input( 'email', $t_user['email'] );
				echo '</td>';
			} ?>
			</tr>
            <!-- project -->
            <tr>
                <td class="category">
                    <?php echo lang_get( 'project_name' ) ?>
                </td>
                <td>
                    <select id="select-project-id" name="project_id" class="input-sm">
                        <?php print_project_option_list( $t_project_id, false, $t_project_id, true, true ) ?>
                    </select>
                </td>
            </tr>
            <?php
            if( OFF == config_get( 'send_reset_password' ) )  { ?>
                <tr>
                    <td class="category">
                        <?php echo lang_get( 'password' ) ?>
                    </td>
                    <td>
                        <input type="password" id="user-password" name="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" />
                    </td>
                </tr>
                <td class="category">
                    <?php echo lang_get( 'verify_password' ) ?>
                </td>
                <td>
                    <input type="password" id="user-verify-password" name="password_verify" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" />
                </td>
                </tr><?php
            } ?>
			<!-- Access Level -->
			<tr>
				<td class="category">
					<?php echo lang_get( 'access_level_label' ) ?>
				</td>
				<td>
					<select id="edit-access-level" name="access_level" class="input-sm"><?php
						$t_access_level = $t_user['access_level'];
						if( !MantisEnum::hasValue( config_get( 'access_levels_enum_string' ), $t_access_level ) ) {
							$t_access_level = config_get( 'default_new_account_access_level' );
						}
						print_project_access_levels_option_list( (int)$t_access_level ); ?>
					</select>
				</td>
			</tr>
			<!-- Enabled Checkbox -->
			<tr>
				<td class="category">
					<?php echo lang_get( 'enabled_label' ) ?>
				</td>
				<td>
					<label>
						<input type="checkbox" class="ace" id="edit-enabled" name="enabled" <?php check_checked( (int)$t_user['enabled'], ON ); ?>>
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			<!-- Protected Checkbox -->
			<tr>
				<td class="category">
					<?php echo lang_get( 'protected_label' ) ?>
				</td>
				<td>
					<label>
						<input type="checkbox" class="ace" id="edit-protected" name="protected" <?php check_checked( (int)$t_user['protected'], ON ); ?>>
						<span class="lbl"></span>
					</label>
				</td>
			</tr>

            <tr>
				<td class="category">
					<?php echo lang_get( 'user_type' ) ?>
				</td>
				<td>
					<label>
						<label class="label label-info"><?php echo lang_get( 'reqer' ) ?></label> <input type="radio"   name="user_type" value="1" <?php echo check_checked_user_type($user_type,1) ?>>
						<label class="label label-info"><?php echo lang_get( 'dever' ) ?></label><input type="radio"   name="user_type" value="2" <?php echo check_checked_user_type($user_type,2) ?>>
						<label class="label label-info"><?php echo lang_get( 'tester' ) ?></label><input type="radio"   name="user_type" value="3" <?php echo check_checked_user_type($user_type,3) ?>>
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			<!-- Submit Button -->
		</fieldset>
		</table>
		</div>
		</div>
		</div>

		<div class="widget-toolbox padding-8 clearfix">
			<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'update_user_button' ) ?>" />
			<?php
			if( config_get( 'enable_email_notification' ) == ON ) { ?>
				&nbsp;
				<label class="inline">
					<input type="checkbox" class="ace" id="send-email" name="send_email_notification" checked="checked">
					<span class="lbl"> <?php echo lang_get( 'notify_user' ) ?></span>
				</label>
			<?php } ?>
		</div>
		</div>
		</div>
	</form>
</div>
<div class="space-10"></div>
<?php
# User action buttons: RESET/UNLOCK and DELETE

$t_reset = $t_user['id'] != auth_get_current_user_id()
	&& ( $t_user['enabled'] == NO)
	&& ( $t_user['protected'] != NO);
$t_unlock = OFF != config_get( 'max_failed_login_count' ) && $t_user['failed_login_count'] > 0;
$t_delete = true;
$t_impersonate = true;

if( $t_reset || $t_unlock || $t_delete || $t_impersonate ) {
?>
<div id="manage-user-actions-div" class="col-md-6 col-xs-12 no-padding">
<div class="space-8"></div>
<div class="btn-group">

<!-- Reset/Unlock Button -->
<?php if( $t_reset || $t_unlock ) { ?>
	<form id="manage-user-reset-form" method="post" action="manage_user_reset.php" class="pull-left">
		<fieldset>
			<?php echo form_security_field( 'manage_user_reset' ) ?>
			<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
<?php	if( $t_reset ) { ?>
			<span><input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'reset_password_button' ) ?>" /></span>
<?php	} else { ?>
			<span><input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'account_unlock_button' ) ?>" /></span>
<?php	} ?>
		</fieldset>
	</form>
<?php } ?>

<!-- Delete Button -->
<?php if( $t_delete ) { ?>
	<form id="manage-user-delete-form" method="post" action="manage_user_delete.php" class="pull-left">
		<fieldset>
			<?php echo form_security_field( 'manage_user_delete' ) ?>
			<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
			<span><input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'delete_user_button' ) ?>" /></span>
		</fieldset>
	</form>
<?php } ?>

<!-- Impersonate Button -->
<?php if( $t_impersonate ) { ?>
	<form id="manage-user-impersonate-form" method="post" action="manage_user_impersonate.php" class="pull-left">
		<fieldset>
			<?php echo form_security_field( 'manage_user_impersonate' ) ?>
			<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
			<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'impersonate_user_button' ) ?>" />
		</fieldset>
	</form>
<?php } ?>

</div>
</div>
<?php } ?>

<?php if( $t_reset ) { ?>
<div class="col-md-6 col-xs-12 no-padding">
<div class="space-4"></div>
<div class="alert alert-info">
	<i class="fa fa-info-circle"></i>
<?php
	if( ( ON == config_get( 'send_reset_password' ) ) && ( ON == config_get( 'enable_email_notification' ) ) ) {
		echo lang_get( 'reset_password_msg' );
	} else {
		echo lang_get( 'reset_password_msg2' );
	}
?>
</div>
</div>
<?php } ?>

<div class="clearfix"></div>

</div>
<?php
layout_page_end();
