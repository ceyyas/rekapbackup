const body = document.querySelector("body"),
      sidebar = document.querySelector(".sidebar"),
      toggle = document.querySelector(".toggle"),
      profile = document.querySelector(".profile"),
      submenu = document.querySelector(".sub-menu"),
      subkategori = document.querySelector(".sub-kategori"),
      toggleButton = document.querySelector(".arrow-dropdown"),
      toggleKategori = document.querySelector(".arrow-dropdown-kategori")

      toggle.addEventListener("click", () =>{
        sidebar.classList.toggle("close");
      });

      toggleButton.addEventListener("click", () =>{
        submenu.classList.toggle("close");
      });

      toggleKategori.addEventListener("click", () => {
        subkategori.classList.toggle("close");
      })

      function selectOption(option) {
        document.getElementById("selected-option").innerText = option;
        document.querySelector(".sub-kategori").classList.add("close");
    }

      function resetForm() {
        const form = document.getElementById('barang-form');
        // Reset form fields except dropdown
        form.reset();
        // Optionally, you could reset the dropdown to the initial value
        document.getElementById('selected-option').textContent = '{{ $rsetbarang->kategori ?? "Kategori" }}';
    }
    
    function toggleDropdown() {
      document.getElementById('customBarangDropdown').classList.toggle('close');
  }
  
  function selectBarang(id, displayText) {
      document.getElementById('barangSelect').value = id; // Set the hidden select value
      document.getElementById('selected-option').textContent = displayText; // Display selected text
      document.getElementById('customBarangDropdown').classList.add('close'); // Close custom dropdown
  }
  
  function updateSelectedOption() {
      const select = document.getElementById('barangSelect');
      const selectedOption = select.options[select.selectedIndex].text;
      document.getElementById('selected-option').textContent = selectedOption;
  }
  
  // Close dropdown when clicking outside
  document.addEventListener('click', function(event) {
      const isClickInside = document.querySelector('.kategori-menu').contains(event.target);
      if (!isClickInside) {
          document.getElementById('customBarangDropdown').classList.add('close');
      }
  });
  

      
