<table class="min-w-full table-auto md:table-fixed divide-y divide-gray-200">
    <thead class="bg-gray-50">
    <tr>
        <th class="py-3 px-6 text-left">Pessoa</th>
        <th class="py-3 px-6 text-left">Benefício</th>
        <th class="py-3 px-6 text-left">Registro</th>
        <th class="py-3 px-6 text-left">Entrega</th>
        <th class="py-3 px-6 text-right">Ações</th>
    </tr>
    </thead>
    <tbody id="deliveries-table-body">
    @include('benefit-deliveries._table_body', ['deliveries' => $benefitDeliveries])
    </tbody>
</table>
<div id="pagination-links" class="mt-3">
    {{ $benefitDeliveries->links('vendor.pagination.custom') }}
</div>
