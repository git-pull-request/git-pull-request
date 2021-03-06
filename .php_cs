<?php

$header = <<<EOF
This file is part of git-pull-request/git-pull-request.

(c) Julien Dufresne <https://github.com/git-pull-request/git-pull-request>

For the full copyright and license information, please view
the LICENSE file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules(
        [
//            '@PSR2' => true,
            'align_double_arrow' => true, // conflict with @Symfony
            'align_equals' => true, // conflict with @Symfony
            'binary_operator_spaces' => true, // @Symfony
            'blank_line_after_namespace' => true, // @PSR2, @Symfony
            'blank_line_after_opening_tag' => true, // @Symfony
            'blank_line_before_return' => true, // @Symfony
            'braces' => true, // @PSR2, @Symfony
            'cast_spaces' => true, // @Symfony
            'class_definition' => true, // @PSR2, @Symfony
            'combine_consecutive_unsets' => true,
            'concat_with_spaces' => false,
            'concat_without_spaces' => true, // @Symfony
            'dir_constant' => false,
            'echo_to_print' => false,
            'elseif' => true, // @PSR2, @Symfony
            'encoding' => true, // @PSR1, @PSR2, @Symfony
            'ereg_to_preg' => false,
            'full_opening_tag' => true, // @PSR1, @PSR2, @Symfony
            'function_declaration' => true, // @PSR2, @Symfony
            'function_typehint_space' => true, // @Symfony
            'general_phpdoc_annotation_remove' => false,
            'general_phpdoc_annotation_rename' => false,
            'hash_to_slash_comment' => true, // @Symfony
            'header_comment' => ['header' => $header],
            'heredoc_to_nowdoc' => true, // @Symfony
            'include' => true, // @Symfony
            'linebreak_after_opening_tag' => false,
            'long_array_syntax' => false,
            'lowercase_cast' => true, // @Symfony
            'lowercase_constants' => true, // @PSR2, @Symfony
            'lowercase_keywords' => true, // @PSR2, @Symfony
            'method_argument_space' => true, // @PSR2, @Symfony
            'method_separation' => true, // @Symfony
            'modernize_types_casting' => true,
            'native_function_casing' => true, // @Symfony
            'new_with_braces' => true, // @Symfony
            'no_alias_functions' => true, // @Symfony
            'no_blank_lines_after_class_opening' => true, // @Symfony
            'no_blank_lines_after_phpdoc' => true, // @Symfony
            'no_blank_lines_before_namespace' => false,
            'no_closing_tag' => true, // @PSR2, @Symfony
            'no_empty_comment' => true, // @Symfony
            'no_empty_phpdoc' => true, // @Symfony
            'no_empty_statement' => true, // @Symfony
            'no_extra_consecutive_blank_lines' => true, // @Symfony
            'no_leading_import_slash' => true, // @Symfony
            'no_leading_namespace_whitespace' => true, // @Symfony
            'no_multiline_whitespace_around_double_arrow' => true, // @Symfony
            'no_multiline_whitespace_before_semicolons' => false,
            'no_php4_constructor' => true,
            'no_short_bool_cast' => true, // @Symfony
            'no_short_echo_tag' => true,
            'no_singleline_whitespace_before_semicolons' => true, // @Symfony
            'no_spaces_after_function_name' => true, // @PSR2, @Symfony
            'no_spaces_inside_offset' => true, // @Symfony
            'no_spaces_inside_parenthesis' => true, // @PSR2, @Symfony
            'no_tab_indentation' => true, // @PSR2, @Symfony
            'no_trailing_comma_in_list_call' => true, // @Symfony
            'no_trailing_comma_in_singleline_array' => true, // @Symfony
            'no_trailing_whitespace' => true, // @PSR2, @Symfony
            'no_trailing_whitespace_in_comment' => true, // @PSR2, @Symfony
            'no_unneeded_control_parentheses' => true, // @Symfony
            'no_unreachable_default_argument_value' => true, // @Symfony
            'no_unused_imports' => true, // @Symfony
            'no_useless_else' => true,
            'no_useless_return' => true,
            'no_whitespace_before_comma_in_array' => true, // @Symfony
            'no_whitespace_in_blank_lines' => true, // @Symfony
            'not_operator_with_space' => false,
            'not_operator_with_successor_space' => false,
            'object_operator_without_whitespace' => true, // @Symfony
            'ordered_class_elements' => true,
            'ordered_imports' => true,
            'php_unit_construct' => false,
            'php_unit_dedicate_assert' => false,
            'php_unit_strict' => false,
            'phpdoc_align' => true, // @Symfony
            'phpdoc_indent' => true, // @Symfony
            'phpdoc_inline_tag' => true, // @Symfony
            'phpdoc_no_access' => true, // @Symfony
            'phpdoc_no_empty_return' => true, // @Symfony
            'phpdoc_no_package' => true, // @Symfony
            'phpdoc_order' => true,
            'phpdoc_property' => false,
            'phpdoc_scalar' => true, // @Symfony
            'phpdoc_separation' => true, // @Symfony
            'phpdoc_single_line_var_spacing' => true, // @Symfony
            'phpdoc_summary' => true, // @Symfony
            'phpdoc_to_comment' => true, // @Symfony
            'phpdoc_trim' => true, // @Symfony
            'phpdoc_type_to_var' => true, // @Symfony
            'phpdoc_types' => true, // @Symfony
            'phpdoc_var_to_type' => false,
            'phpdoc_var_without_name' => true, // @Symfony
            'pre_increment' => true, // @Symfony
            'print_to_echo' => true, // @Symfony
            'psr0' => false,
            'random_api_migration' => false,
            'self_accessor' => true, // @Symfony
            'short_array_syntax' => true,
            'short_scalar_cast' => true, // @Symfony
            'simplified_null_return' => true, // @Symfony
            'single_blank_line_at_eof' => true, // @PSR2, @Symfony
            'single_blank_line_before_namespace' => true, // @Symfony
            'single_import_per_statement' => true, // @PSR2, @Symfony
            'single_line_after_imports' => true, // @PSR2, @Symfony
            'single_quote' => true, // @Symfony
            'space_after_semicolon' => true, // @Symfony
            'standardize_not_equals' => true, // @Symfony
            'strict_comparison' => true,
            'strict_param' => true,
            'switch_case_semicolon_to_colon' => true, // @PSR2, @Symfony
            'switch_case_space' => true, // @PSR2, @Symfony
            'ternary_operator_spaces' => true, // @Symfony
            'trailing_comma_in_multiline_array' => true, // @Symfony
            'trim_array_spaces' => true, // @Symfony
            'unalign_double_arrow' => false, // @Symfony
            'unalign_equals' => false, // @Symfony
            'unary_operator_spaces' => true, // @Symfony
            'unix_line_endings' => true, // @PSR2, @Symfony
            'visibility_required' => true, // @PSR2, @Symfony
            'whitespace_after_comma_in_array' => true, // @Symfony
        ]
    )
    ->finder($finder)
    ;