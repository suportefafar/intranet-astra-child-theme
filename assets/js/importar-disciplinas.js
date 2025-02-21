/*
 * Aguarda até que a DOM seja carregada para inserir os eventos no calendário
 */
document.addEventListener("DOMContentLoaded", () => {
  console.log(csvData);
});

// document
//   .querySelector("#btn_import")
//   .addEventListener("click", importClassSubjects);

// async function importClassSubjects() {
//   showAlert("Importando disciplinas.... ", "info");

//   try {
//     const response = await axios.post("/importar-disciplinas", {
//       class_subjects: csvData,
//     });

//     console.log(response);
//   } catch (error) {
//     console.log(error.response.data.message);
//     return [];
//   }

//   showAlert("Concluído!", "success", true);
// }
