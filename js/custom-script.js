jQuery(document).ready(function ($) {
  var start = 0;
  var batchSize = 3; // Tamaño del lote

  function leerCSV() {
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "leer_csv_ajax", // Nombre de la acción AJAX
        start: start, // Punto de inicio del lote
      },
      success: function (response) {
        if (response.success) {
          if (Array.isArray(response.data.data)) {
            var csvData = response.data.data;

            var html = "<ul>";
            csvData.forEach(function (row) {
              html += "<li>" + row.join(", ") + "</li>"; // Mostrar cada fila como una lista
            });
            html += "</ul>";
            $("#csvRows").append(html); // Agregar las filas al contenedor
            start = response.data.next; // Actualizar el punto de inicio para el siguiente lote
            if (csvData.length === batchSize) {
              // Si hay más datos, continuar con el siguiente lote y actualizar el indicador de lote
              $("#loteActual").text("Lote actual: " + (start / batchSize + 1));
              leerCSV();
            } else {
              $("#loteActual").text("Proceso de lectura completado.");
            }
          } else {
            console.error(
              "Los datos recibidos no son un array:",
              response.data
            );
          }
        } else {
          console.error("Error al leer archivo CSV:", response.data);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error al realizar la solicitud AJAX:", error);
      },
    });
  }

  $("#btnLeerCSV").on("click", function (e) {
    e.preventDefault();
    start = 0; // Restablecer el punto de inicio al hacer clic en el botón
    $("#csvRows").empty(); // Vaciar el contenedor antes de cargar los datos
    $("#loteActual").text("Lote actual: 1"); // Mostrar el primer lote al inicio
    leerCSV(); // Iniciar la lectura del CSV por lotes
  });
});
