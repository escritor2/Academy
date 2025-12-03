<script>
    // AQUI A MÁGICA ACONTECE!
    // O PHP vai "imprimir" o JSON aqui dentro antes de enviar pro navegador.
    
    const treinosVindosDoPHP = <?php echo json_encode($treinosOrganizados); ?>;

    /*
      O que o navegador vai ver (View Source) será algo assim:
      const treinosVindosDoPHP = {"A":[{"name":"Supino"...}], "B":[]};
      
      O PHP sumiu, ficou só o dado puro!
    */
</script>

<script src="paginacliente.js"></script>