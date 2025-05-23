function cari() {
  const input = document.getElementById("cariDokter").value;
  const hasil = document.getElementById("hasilCari");

  if (input.trim() === "") {
    hasil.textContent = "Silakan masukkan nama dokter.";
  } else {
    hasil.textContent = `Hasil pencarian untuk: ${input}`;
  }
}

const contentData = {
  Fisioterapi: {
    title: "Fisioterapi",
    description:
      "Unit rawat inap kami yang dilengkapi berbagai fasilitas untuk meningkatkan kenyamanan Anda selama perawatan di RSUI.",
    images: [
      "https://via.placeholder.com/400x250?text=Admisi+1",
      "https://via.placeholder.com/200x150?text=Admisi+2",
      "https://via.placeholder.com/200x150?text=Admisi+3",
      "https://via.placeholder.com/200x150?text=Admisi+4",
    ],
  },
  radiologi: {
    title: "Radiologi",
    description:
      "Pemeriksaan radiologi lengkap seperti CT-Scan, MRI, dan X-ray untuk diagnosis tepat.",
    images: [
      "https://via.placeholder.com/400x250?text=Hemodialisis+1",
      "https://via.placeholder.com/200x150?text=Hemodialisis+2",
    ],
  },
  lab: {
    title: "Laboratorium",
    description:
      "Laboratorium modern untuk menunjang diagnosis penyakit dengan hasil cepat dan akurat.",
    images: ["https://via.placeholder.com/400x250?text=Lab+1"],
  },
  farmasi: {
    title: "Farmasi",
    description:
      "Pelayanan farmasi 24 jam dengan berbagai obat generik dan paten berkualitas.",
    images: ["https://via.placeholder.com/400x250?text=Farmasi+1"],
  },
  radiologi: {
    title: "Radiologi",
    description:
      "Pemeriksaan radiologi lengkap seperti CT-Scan, MRI, dan X-ray untuk diagnosis tepat.",
    images: [
      "Asset/Ruang Radiologi.jpg",,
    ],
  },
  rawatInap: {
    title: "Rawat Inap",
    description:
      "Kami menyediakan kamar rawat inap yang bersih dan nyaman, dengan peralatan medis lengkap dan privasi bagi pasien.",
    images: ["Asset/Ruang rawat inap.jpg"],
  },
  babySpa: {
    title: "Baby Spa",
    description:
      "Layanan Baby Spa untuk bayi Anda dengan fasilitas steril, aman, dan diawasi oleh terapis profesional.",
    images: ["Asset/"],
  },
};

function showContent(key, element) {
  const data = contentData[key];
  const contentArea = document.getElementById("content-area");

  // Update sidebar active state
  document
    .querySelectorAll(".sidebar li")
    .forEach((li) => li.classList.remove("active"));
  if (element) {
    element.classList.add("active");
  }

  // Build HTML content
  let detailLink = "";
  if (key.toLowerCase() === "radiologi") {
    detailLink = `<a href="detailradiologi.php" class="btn-selengkapnya">Selengkapnya</a>`;
  } else {
    detailLink = `<a href="#" class="btn-selengkapnya" onclick="alert('Detail belum tersedia!');return false;">Selengkapnya</a>`;
  }

  contentArea.innerHTML = `
    <h2>${data.title}</h2>
    <p>${data.description}</p>
    ${detailLink}
    <div class="images">
      ${data.images
        .map((src) => `<img src="${src}" alt="${data.title}">`)
        .join("")}
    </div>
  `;
}

// Show default
window.onload = () => {
  const sidebarItems = document.querySelectorAll(".sidebar li");
  showContent("Fisioterapi", sidebarItems[0]);
};

// Show read more
function readMore() {
  alert("Fitur belum tersedia. Akan diarahkan ke halaman artikel penuh.");
}
function initialize() {
    var koordinatTujuan = { lat: -6.238077422724989, lng: 106.7691991230291 }; // RS Kartini
    var propertiPeta = { 
      center: koordinatTujuan,
      zoom: 16,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var peta = new google.maps.Map(document.getElementById("googleMap"), propertiPeta);
    var marker = new google.maps.Marker({
      position: koordinatTujuan,
      map: peta,
      title: "Rumah Sakit Kartini"
    });
  }

  function showFullImage(src) {
    var modal = document.getElementById('modalFullImage');
    var img = document.getElementById('modalImg');
    img.src = src;
    modal.style.display = 'flex';
  }

  function closeFullImage(e) {
    if (!e || e.target.classList.contains('modal-full-img') || e.target.classList.contains('modal-close')) {
      document.getElementById('modalFullImage').style.display = 'none';
    }
  }

  // === CARI DOKTER DROPDOWN ===
  // Ambil data dokter dari database via AJAX
  let daftarDokterDB = [];

  function fetchDokterDropdown(keyword = "") {
    fetch('dropdown_dokter.php?keyword=' + encodeURIComponent(keyword))
      .then(res => res.json())
      .then(data => {
        daftarDokterDB = data;
        renderCustomDropdown();
      });
  }

  function showCustomDropdown() {
    const val = document.getElementById('cariDokter').value.trim();
    fetchDokterDropdown(val);
    document.getElementById('customDropdown').style.display = "block";
  }

  function updateCustomDropdown() {
    const val = document.getElementById('cariDokter').value.trim();
    fetchDokterDropdown(val);
  }

  function renderCustomDropdown() {
    const input = document.getElementById('cariDokter');
    const dropdown = document.getElementById('customDropdown');
    const inputId = document.getElementById('dokterIdTerpilih');
    dropdown.innerHTML = "";
    if (daftarDokterDB.length > 0) {
      daftarDokterDB.forEach((dokter) => {
        const item = document.createElement('div');
        item.className = "custom-dropdown-item";
        item.tabIndex = 0;
        item.style.display = "flex";
        item.style.alignItems = "center";
        item.style.justifyContent = "flex-start";
        item.style.padding = "14px 18px";
        item.style.fontSize = "0.97rem";
        item.style.fontFamily = "'Segoe UI','Poppins','Montserrat',Arial,sans-serif";
        item.style.color = "#222";
        item.style.cursor = "pointer";
        item.style.transition = "background 0.15s,color 0.15s";
        item.style.textAlign = "left";
        item.innerHTML = `<span style="flex:1;font-weight:500;text-align:left;">${dokter.nama} <span style="color:#b0b0b0;font-weight:400;">- ${dokter.spesialisasi}</span></span>`;
        item.onmousedown = function(e) {
          e.preventDefault();
          input.value = dokter.nama;
          inputId.value = dokter.id ? dokter.id : '';
          dropdown.style.display = "none";
        };
        item.onmouseover = function() {
          this.style.background = "#fff7f0";
          this.style.color = "#f47b20";
        };
        item.onmouseout = function() {
          this.style.background = "#fff";
          this.style.color = "#222";
        };
        dropdown.appendChild(item);
      });
      dropdown.style.display = "block";
    } else {
      dropdown.style.display = "none";
    }
  }

 

  // Hide dropdown if click outside
  document.addEventListener('mousedown', function(e) {
    const dropdown = document.getElementById('customDropdown');
    const input = document.getElementById('cariDokter');
    if (!dropdown.contains(e.target) && e.target !== input) {
      dropdown.style.display = "none";
    }
  });

  // Keyboard navigation for custom dropdown
  document.getElementById('cariDokter').addEventListener('keydown', function(e) {
    const dropdown = document.getElementById('customDropdown');
    const items = dropdown.querySelectorAll('.custom-dropdown-item');
    if (dropdown.style.display === "block" && items.length > 0) {
      let idx = -1;
      for (let i = 0; i < items.length; i++) {
        if (document.activeElement === items[i]) {
          idx = i;
          break;
        }
      }
      if (e.key === "ArrowDown") {
        e.preventDefault();
        if (idx < items.length - 1) {
          items[idx + 1].focus();
        } else if (idx === -1) {
          items[0].focus();
        }
      } else if (e.key === "ArrowUp") {
        e.preventDefault();
        if (idx > 0) {
          items[idx - 1].focus();
        }
      } else if (e.key === "Enter" && idx >= 0) {
        items[idx].dispatchEvent(new MouseEvent('mousedown'));
      }
    }
  });

  function cari() {
    const nama = document.getElementById('cariDokter').value.trim();
    if (nama === "") {
      document.getElementById('hasilCari').textContent = "Silakan masukkan nama dokter.";
      return;
    }
    if (daftarDokter.some(d => d.nama === nama)) {
      document.getElementById('hasilCari').textContent = "Dokter ditemukan: " + nama;
    } else {
      document.getElementById('hasilCari').textContent = "Dokter tidak ditemukan.";
    }
    document.getElementById('dropdownDokter').style.display = "none";
  }

  // Ganti/override fungsi submit form agar jika ada dokterIdTerpilih langsung redirect ke profil dokter
  document.getElementById('formCariDokter').addEventListener('submit', function(e) {
    e.preventDefault();
    var nama = document.getElementById('cariDokter').value.trim();
    var dokterId = document.getElementById('dokterIdTerpilih').value;
    var hasil = document.getElementById('hasilCari');
    hasil.textContent = '';
    if (!nama) {
      hasil.textContent = 'Silakan masukkan nama dokter.';
      return;
    }
    // Pastikan dokterId valid (bukan kosong, bukan undefined, bukan null, bukan string 'undefined')
    if (dokterId && dokterId !== 'undefined' && dokterId !== 'null') {
      window.location.href = 'profil_dokter.php?id=' + encodeURIComponent(dokterId);
      return;
    }
    // Jika tidak ada ID, cari berdasarkan nama
    fetch('cari_dokter.php?nama=' + encodeURIComponent(nama))
      .then(response => response.json())
      .then(data => {
        if (data.success && data.id) {
          window.location.href = 'profil_dokter.php?id=' + data.id;
        } else {
          hasil.textContent = 'Dokter tidak ditemukan.';
        }
      })
      .catch(() => {
        hasil.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
      });
  });

