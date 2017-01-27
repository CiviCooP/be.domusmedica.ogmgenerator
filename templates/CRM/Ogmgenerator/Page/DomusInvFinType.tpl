<div class="crm-content-block crm-block">
  <div id="help">
    Hieronder staan alle Financiële Types en of het systeem een Domus Factuurnummer genereert
  </div>
  <div id="domus_inv_fin_type-wrapper" class="dataTables_wrapper">
    <table id="domus_fin_inv_type-table" class="display">
      <thead>
      <tr>
        <th class="sorting-disabled" rowspan="1" colspan="1">{ts}Financial Type{/ts}</th>
        <th class="sorting-disabled" rowspan="1" colspan="1">{ts}Domus Factuurnummering toepassen?{/ts}</th>
        <th class="sorting_disabled" rowspan="1" colspan="1"></th>
      </tr>
      </thead>
      <tbody>
      {assign var="row_class" value="odd-row"}
      {foreach from=$domus_inv_fin_types key=domus_inv_id item=domus_inv_fin_type}
        <tr id="row_{$domus_inv_id}" class={$row_class}>
          <td hidden="1">{$domus_inv_id}</td>
          <td>{$domus_inv_fin_type.financial_type_label}</td>
          <td>
            {if $domus_inv_fin_type.is_domus_invoice eq 1}
              {ts}Yes{/ts}
            {else}
              {ts}No{/ts}
            {/if}
          </td>
          <td>
              <span>
                {if $domus_inv_fin_type.is_domus_invoice eq 1}
                  <a href="{crmURL p='civicrm/domusinvfintypelist' q='reset=1'}" onclick="disableDomusInvoicing({$domus_inv_id})">{ts}uitschakelen{/ts}</a>
                {else}
                  <a href="{crmURL p='civicrm/domusinvfintypelist' q='reset=1'}" onclick="enableDomusInvoicing({$domus_inv_id})">{ts}inschakelen{/ts}</a>
                {/if}
              </span>
          </td>
        </tr>
        {if $row_class eq "odd-row"}
          {assign var="row_class" value="even-row"}
        {else}
          {assign var="row_class" value="odd-row"}
        {/if}
      {/foreach}
      </tbody>
    </table>
  </div>
</div>
{literal}
  <script type="text/javascript">
    function enableDomusInvoicing(domusInvoiceId) {
      CRM.api3('DomusInvFinType', 'enable', {"id": domusInvoiceId});
    }
    function disableDomusInvoicing(domusInvoiceId) {
      CRM.api3('DomusInvFinType', 'disable', {"id": domusInvoiceId});
    }
  </script>
{/literal}