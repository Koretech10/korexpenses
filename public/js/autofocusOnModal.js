/**
 * Met en évidence le premier champ input de la modal modalId
 * @param {string} modalId (précédé d'un #)
 */
function autofocusOnModal(modalId)
{
    $(modalId).on('shown.bs.modal', function () {
        $(modalId).find('input, select').first().trigger('focus')
    })
}