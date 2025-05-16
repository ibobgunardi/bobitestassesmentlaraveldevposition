// Filter & Search Utilities

export function setupFilters(searchInputId, projectFilterId, clearFiltersBtnId, filterCallback) {
    const searchInput = document.getElementById(searchInputId);
    const projectFilter = document.getElementById(projectFilterId);
    if (searchInput && projectFilter) {
        const filterTasks = () => {
            filterCallback(searchInput.value, projectFilter.value);
        };
        searchInput.addEventListener('input', filterTasks);
        projectFilter.addEventListener('change', filterTasks);
        const clearFiltersBtn = document.getElementById(clearFiltersBtnId);
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', () => {
                searchInput.value = '';
                projectFilter.value = '';
                filterTasks();
            });
        }
    }
}
