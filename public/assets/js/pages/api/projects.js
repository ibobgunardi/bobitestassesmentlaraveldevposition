// public/assets/js/modules/api/projects.js
export const ProjectsAPI = {
    async fetchProjects() {
        const response = await fetch('/api/projects');
        if (!response.ok) {
            throw new Error('Failed to load projects');
        }
        return response.json();
    }
};