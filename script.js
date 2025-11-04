 // Array com as URLs das imagens
 const images = [
  "./img/info-prod2.jpeg",
  "./img/info-prod.jpeg",
  "./img/info-prod1.jpg",
  "./img/info-prod3.jpeg"
];

let currentIndex = 0;
const mainImage = document.getElementById("mainImage");
const thumbnails = document.getElementById("thumbnails");

function updateMainImage(index) {
  currentIndex = index;
  mainImage.src = images[index];
  document.querySelectorAll(".thumbs img").forEach((img, i) => {
    img.classList.toggle("active", i === index);
  });
}

function prevImage() {
  const newIndex = (currentIndex - 1 + images.length) % images.length;
  updateMainImage(newIndex);
}

function nextImage() {
  const newIndex = (currentIndex + 1) % images.length;
  updateMainImage(newIndex);
}

// Criar miniaturas dinamicamente
images.forEach((src, index) => {
  const img = document.createElement("img");
  img.src = src;
  img.alt = "Miniatura " + (index + 1);
  img.onclick = () => updateMainImage(index);
  if (index === 0) img.classList.add("active");
  thumbnails.appendChild(img);
});