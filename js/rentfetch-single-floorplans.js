function rentfetchActivateFloorplanMediaTab(tab) {
	const tabList = tab.closest('[role="tablist"]');
	const mediaColumn = tab.closest('.images-column');
	if (!tabList || !mediaColumn) {
		return;
	}

	tabList.querySelectorAll('[role="tab"]').forEach((candidate) => {
		const selected = candidate === tab;
		candidate.setAttribute('aria-selected', selected ? 'true' : 'false');
		candidate.tabIndex = selected ? 0 : -1;
	});
	mediaColumn.querySelectorAll('[data-floorplan-media-panel]').forEach((panel) => {
		panel.hidden = panel.dataset.floorplanMediaPanel !== tab.dataset.floorplanMediaTab;
	});
}

document.addEventListener('click', (event) => {
	const tab = event.target.closest('[data-floorplan-media-tab]');
	if (tab) {
		rentfetchActivateFloorplanMediaTab(tab);
	}
});

document.addEventListener('keydown', (event) => {
	const tab = event.target.closest('[data-floorplan-media-tab]');
	if (!tab || !['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
		return;
	}

	const tabs = Array.from(tab.parentElement.querySelectorAll('[data-floorplan-media-tab]'));
	let index = tabs.indexOf(tab);
	if (event.key === 'Home') {
		index = 0;
	} else if (event.key === 'End') {
		index = tabs.length - 1;
	} else {
		index = (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
	}

	event.preventDefault();
	tabs[index].focus();
	rentfetchActivateFloorplanMediaTab(tabs[index]);
});
