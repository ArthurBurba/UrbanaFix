document.addEventListener("DOMContentLoaded", () => {
  const porcentagens = document.querySelectorAll(".percent");
  const grafico = document.getElementById("grafico");
  const section = document.getElementById("dados");

  // só roda o gráfico e os números se for a página inicial
  if (grafico && section) {
    let animado = false;

    function animarNumeros() {
      porcentagens.forEach(span => {
        const valor = parseFloat(span.dataset.value);
        let atual = 0;
        const intervalo = setInterval(() => {
          atual += 1;
          span.textContent = atual + "%";
          if (atual >= Math.round(valor)) clearInterval(intervalo);
        }, 67);
      });
    }

    function criarGrafico() {
      const ctx = grafico.getContext("2d");
      new Chart(ctx, {
        type: "pie",
        data: {
          labels: [
            "Violência urbana",
            "Violência doméstica",
            "Violência contra jovens",
            "Todas as anteriores"
          ],
          datasets: [{
            data: [35.9, 19.5, 12.3, 32.3],
            backgroundColor: [
              "#007bff", 
              "#dc3545", 
              "#ffc107", 
              "#ff7a00"  
            ],
            borderWidth: 1,
            borderColor: "#fff"
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: "bottom",
              labels: { color: "#333", font: { size: 14 } }
            }
          },
          animation: { animateRotate: true, duration: 2400 }
        }
      });
    }

    const observer = new IntersectionObserver(entries => {
      if (entries[0].isIntersecting && !animado) {
        animado = true;
        animarNumeros();
        criarGrafico();
      }
    }, { threshold: 0.5 });

    observer.observe(section);
  }
});
