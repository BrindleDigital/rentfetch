document.addEventListener('DOMContentLoaded', function () {
    const dialog = document.getElementById('search-filters');
    const openButton = document.getElementById('open-search-filters');
    const submitButton = document.getElementById('submit-filters');

    openButton.addEventListener('click', function () {
        dialog.showModal();
    });

    dialog.addEventListener('click', function (event) {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    const showPropertiesButton = document.getElementById('show-properties');
    showPropertiesButton.addEventListener('click', function () {
        dialog.close();
    });
});
