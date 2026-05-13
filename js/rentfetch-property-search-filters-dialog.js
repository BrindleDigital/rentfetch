document.addEventListener('DOMContentLoaded', function () {
    const dialog = document.getElementById('search-filters');
    const openButton = document.getElementById('open-search-filters');
    const showPropertiesButton = document.getElementById('show-properties');

    if (!dialog || !openButton) {
        return;
    }

    openButton.addEventListener('click', function (event) {
        event.preventDefault();
        dialog.showModal();
    });

    dialog.addEventListener('pointerdown', function (event) {
        if (!dialog.open) {
            return;
        }

        const dialogRect = dialog.getBoundingClientRect();
        const clickedOutsideDialog =
            event.target === dialog ||
            event.clientX < dialogRect.left ||
            event.clientX > dialogRect.right ||
            event.clientY < dialogRect.top ||
            event.clientY > dialogRect.bottom;

        if (clickedOutsideDialog) {
            event.preventDefault();
            event.stopPropagation();
            dialog.close();
        }
    }, true);

    if (showPropertiesButton) {
        showPropertiesButton.addEventListener('click', function (event) {
            event.preventDefault();
            dialog.close();
        });
    }
});
