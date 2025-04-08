<?php

namespace Hirasso\ACFVimeoField;

/**
 * @var array $atts
 * @var array $field
 * @var string $value
 * @var string $url
 * @var string $html
 */
?>

<div <?= acf_esc_attrs($atts) ?>>

    <?php acf_hidden_input([
        'class' => 'input-value',
        'name' => $field['name'],
        'value' => $value ]) ?>

    <?php acf_text_input([
        'class' => 'input-search',
        'value' => $url ?: "",
        'placeholder' => __("Enter URL", 'acf'),
        'autocomplete' => 'off'
    ]); ?>

    <div class="acf-actions -hover">
        <a data-name="clear-button" href="#" class="acf-icon -cancel grey"></a>
    </div>


	<div class="canvas">
		<div class="canvas-media">
			<?php if ($field['value']):
			    echo $html;
			endif; ?>
	    </div>
		<i class="acf-icon -picture hide-if-value"></i>
	</div>

</div>
