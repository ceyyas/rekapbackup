const body = document.querySelector("body");
const sidebar = document.querySelector(".sidebar");
const toggle = document.querySelector(".toggle");
const profile = document.querySelector(".profile");
const subkategori = document.querySelector(".sub-kategori");

if (toggle) {
    toggle.addEventListener("click", () => {
        sidebar.classList.toggle("close");
    });
}

document.querySelectorAll(".data-induk").forEach(menu => {
    menu.addEventListener("click", function (e) {
        e.preventDefault();

        const parentMenu = this.closest(".menu-link");
        const submenu = parentMenu.querySelector(".sub-menu");

        submenu.classList.toggle("close");
    });
});

function selectOption(option) {
    document.getElementById("selected-option").innerText = option;
    document.querySelector(".sub-kategori").classList.add("close");
}

function resetForm() {
    const form = document.getElementById('barang-form');
    form.reset();
    document.getElementById('selected-option').textContent = "Kategori";
}

function toggleDropdown() {
    document.getElementById('customBarangDropdown').classList.toggle('close');
}

function selectBarang(id, displayText) {
    document.getElementById('barangSelect').value = id;
    document.getElementById('selected-option').textContent = displayText;
    document.getElementById('customBarangDropdown').classList.add('close');
}

function updateSelectedOption() {
    const select = document.getElementById('barangSelect');
    const selectedOption = select.options[select.selectedIndex].text;
    document.getElementById('selected-option').textContent = selectedOption;
}

document.addEventListener('click', function(event) {
    const kategoriMenu = document.querySelector('.kategori-menu');
    const dropdown = document.getElementById('customBarangDropdown');

    if (kategoriMenu && !kategoriMenu.contains(event.target)) {
        dropdown.classList.add('close');
    }
});


