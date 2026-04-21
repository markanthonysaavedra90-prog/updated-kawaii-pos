fetch("dashboard.php")
.then(res => res.json())
.then(data => {

    document.getElementById("totalSales").innerText = data.totalSales || 0;
    document.getElementById("totalTransactions").innerText = data.transactions;

    let table = `
    <tr>
        <th>Product</th>
        <th>Sold</th>
    </tr>`;

    data.bestProducts.forEach(p => {
        table += `
        <tr>
            <td>${p.name}</td>
            <td>${p.total_sold}</td>
        </tr>`;
    });

    document.getElementById("bestTable").innerHTML = table;
});