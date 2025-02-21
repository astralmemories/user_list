(function ($, Drupal) {
    Drupal.behaviors.userList = {
      attach: function (context, settings) {
        $('#user-list-wrapper table', context).each(function () {
          if (!$(this).hasClass('table-bordered table-hover')) {
            $(this).bootstrapTable({
                locale: 'es-ES',
                formatSearch: () => 'Buscar...',
                formatNoMatches: () => 'No se encontraron resultados',
                formatShowingRows: (pageFrom, pageTo, totalRows) => 
                    `Mostrando ${pageFrom} a ${pageTo} de ${totalRows} filas`
            });
          }
        });
      }
    };
})(jQuery, Drupal);
