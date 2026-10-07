document.addEventListener('DOMContentLoaded', function () {
	const field = document.getElementById('rentfetch-property-logo-id');
	const select = document.getElementById('rentfetch-property-logo-select');
	const remove = document.getElementById('rentfetch-property-logo-remove');
	const preview = document.getElementById('rentfetch-property-logo-preview');
	if (!field || !select || !remove || !preview) return;

	let frame;
	select.addEventListener('click', function () {
		if (!frame) {
			frame = wp.media({
				title: select.dataset.title,
				button: { text: select.dataset.select },
				library: { type: 'image' },
				multiple: false,
			});
			frame.on('open', function () {
				const selection = frame.state().get('selection');
				selection.reset();
				if (field.value !== '0' && field.value !== '') {
					selection.add(wp.media.attachment(Number(field.value)));
				}
			});
			frame.on('select', function () {
				const attachment = frame.state().get('selection').first().toJSON();
				const image = document.createElement('img');
				image.src = attachment.sizes?.medium?.url || attachment.url;
				image.alt = attachment.alt || '';
				image.style.cssText = 'max-width:200px;max-height:150px;width:auto;height:auto;';
				preview.replaceChildren(image);
				field.value = attachment.id;
				select.textContent = select.dataset.replace;
				remove.style.display = '';
			});
		}
		frame.open();
	});

	remove.addEventListener('click', function () {
		field.value = '0';
		preview.replaceChildren();
		select.textContent = select.dataset.select;
		remove.style.display = 'none';
		select.focus();
	});
});
