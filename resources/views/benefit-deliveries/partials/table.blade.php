<div class="overflow-x-auto pb-4">
    <table class="divide-y divide-gray-200 w-full">
        <thead class="bg-gray-50">
        <tr>
            <th class="py-3 px-2 md:px-6 text-left">Pessoa</th>
            <th class="py-3 px-2 md:px-6 text-left hidden md:table-cell">Benefício</th>
            <th class="py-3 px-2 md:px-6 text-left hidden md:table-cell">Registro</th>
            <th class="py-3 px-2 md:px-6 text-left hidden md:table-cell">Entrega</th>
            <th class="py-3 px-2 md:px-6 text-right">Ações</th>
        </tr>
        </thead>
        <tbody id="deliveries-table-body">
        @include('benefit-deliveries._table_body', ['deliveries' => $benefitDeliveries])
        </tbody>
    </table>
</div>
<div id="pagination-links">
    {{ $benefitDeliveries->onEachSide($agent->isDesktop() ? 3 : 1)->appends(request()->query())->links() }}
</div>
