import ApexCharts from 'apexcharts';

window.ApexCharts = ApexCharts;

function applyTheme() {
    if (localStorage.getItem('theme') === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
    } else {
        document.documentElement.removeAttribute('data-theme');
    }
}

document.addEventListener('livewire:navigated', applyTheme);
