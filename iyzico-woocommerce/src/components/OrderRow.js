const OrderRow = ({order}) => (
    <tr>
        <td className="px-6 py-4 whitespace-nowrap">
            <div className="text-sm font-medium text-gray-900">{order.id}</div>
        </td>
        <td className="px-6 py-4 whitespace-nowrap">
            <div className="text-sm text-gray-900">{order.customer}</div>
        </td>
        <td className="px-6 py-4 whitespace-nowrap">
            <div className="text-sm text-gray-900">{order.date}</div>
        </td>
        <td className="px-6 py-4 whitespace-nowrap">
            <div className="text-sm text-gray-900">{order.total}</div>
        </td>
        <td className="px-6 py-4 whitespace-nowrap">
            <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                order.status === 'Tamamlandı' ? 'bg-green-100 text-green-800' :
                    order.status === 'İşleniyor' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800'
            }`}>
                {order.status}
            </span>
        </td>
    </tr>
)
export default OrderRow;