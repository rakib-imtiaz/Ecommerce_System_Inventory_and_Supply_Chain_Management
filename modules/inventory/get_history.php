<?php
require_once '../../config/database.php';
require_once '../../classes/Inventory.php';

$database = new Database();
$db = $database->getConnection();
$inventory = new Inventory($db);

// Get product ID from URL
$product_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Get stock history
$history = $inventory->getStockHistory($product_id);
?>

<div class="overflow-x-auto">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Change</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Previous Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">New Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php while ($row = $history->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo date('M d, Y H:i', strtotime($row['change_date'])); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            <?php echo $row['change_type'] === 'addition' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo ucfirst($row['change_type']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo ($row['change_type'] === 'addition' ? '+' : '-') . abs($row['quantity_change']); ?> units
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $row['previous_stock']; ?> units</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $row['new_stock']; ?> units</td>
                    <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($row['notes']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div> 