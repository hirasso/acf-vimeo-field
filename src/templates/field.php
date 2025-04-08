<div <?= acf_esc_attrs($data->atts) ?>>
    <?php acf_hidden_input([ 'class' => 'input-value', 'name' => $data->field['name'], 'value' => $data->value ]); ?>

    <div class="title">
	    <?php acf_text_input([
	        'class' => 'input-search',
	        'value' => $data->url ?: "",
	        'placeholder' => __("Enter URL", 'acf'),
	        'autocomplete' => 'off'
	    ]); ?>

        <div class="acf-actions -hover">
			<a data-name="clear-button" href="#" class="acf-icon -cancel grey"></a>
		</div>
	</div>

	<div class="canvas">
		<div class="canvas-media">
			<?php if ($data->field['value']):
			    echo $data->iframe;
			endif; ?>
	    </div>
		<i class="acf-icon -picture hide-if-value"></i>
	</div>

</div>
