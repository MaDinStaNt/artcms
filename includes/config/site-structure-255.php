<?php
$ss =
array(
	'title' => 'Art-cms', 'short-title' => 'Art-cms', 'mode' => 'hidden',  'tag' => '', 'class' => 'admin/IndexPage.php', 'template' => 'admin/index.tpl', 'children' => array(
		0 => array(
			'title' => 'Login', 'tag' => 'login', 'mode' => 'hidden', 'class' => 'admin/LoginPage.php', 'template' => 'admin/login.tpl', 'children' => array(
			),
		),
		1 => array(
			'title' => 'Users', 'tag' => 'users', 'class' => 'admin/users/UsersPage.php', 'template' => 'admin/users/users.tpl', 'children' => array(
                0 => array(
                        'title' => 'User edit', 'tag' => 'user_edit', 'mode' => 'hidden', 'class' => 'admin/users/UserEditPage.php', 'template' => 'admin/users/user_edit.tpl'
                ),
				1 => array(
					'title' => 'User roles', 'tag' => 'roles', 'class' => 'admin/users/RolesPage.php', 'template' => 'admin/users/roles.tpl', 'children' => array(
		                0 => array(
		                        'title' => 'Role add/edit', 'tag' => 'user_role_edit', 'class' => 'admin/users/UserRoleEditPage.php', 'template' => 'admin/users/user_role_edit.tpl', 'mode' => 'hidden'
		            	),
					),
				),
			),
		),
		2 => array(
			'title' => 'Localizer strings', 'tag' => 'localizer_strings', 'class' => 'admin/localizer/StringsPage.php', 'template' => 'admin/localizer/strings.tpl', 'children' => array(
                0 => array(
                    'title' => 'Localizer string view', 'tag' => 'loc_string_edit', 'mode' => 'hidden', 'class' => 'admin/localizer/StringEditPage.php', 'template' => 'admin/localizer/string_edit.tpl', 'children' => array(
                	),
            	),
				1 => array(
					'title' => 'Languages', 'tag' => 'languages', 'class' => 'admin/localizer/LanguagesPage.php', 'template' => 'admin/localizer/languages.tpl', 'children' => array(
		                0 => array(
		                        'title' => 'Language edit', 'tag' => 'loc_lang_edit', 'mode' => 'hidden', 'class' => 'admin/localizer/LanguageEditPage.php', 'template' => 'admin/localizer/language_edit.tpl'
		            	),
					),
				),
			),
		),
		3 => array(
			'title' => 'Settings', 'tag' => 'registry', 'class' => 'admin/registry/RegistryPage.php', 'template' => 'admin/registry/_registry_out.tpl', 'children' => array(
				0 => array(
					'title' => 'Images', 'tag' => 'image', 'class' => 'admin/registry/ImagePage.php', 'template' => 'admin/registry/image.tpl', 'children' => array(
						0 => array(
								'title' => 'Image view', 'tag' => 'image_edit', 'class' => 'admin/registry/ImageEditPage.php', 'template' => 'admin/registry/image_edit.tpl', 'mode' => 'hidden', 'children' => array(
								0 => array(
										'title' => 'Image size view', 'tag' => 'image_size_edit', 'mode' => 'hidden', 'class' => 'admin/registry/ImageSizeEditPage.php', 'template' => 'admin/registry/image_size_edit.tpl'
								),
							),
						),
					),
				),
				1 => array(
					'title' => 'Registry Path edit', 'tag' => 'path_edit', 'class' => 'admin/registry/PathEditPage.php', 'template' => 'admin/registry/path_edit.tpl', 'mode' => 'hidden', 'children' => array(
						0 => array(
							'title' => 'Path Value edit', 'tag' => 'path_value_edit', 'class' => 'admin/registry/PathValueEditPage.php', 'template' => 'admin/registry/path_value_edit.tpl'
						),
					),
				),
			),
		),
	),
);
?>