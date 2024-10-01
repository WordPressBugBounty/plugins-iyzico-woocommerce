import { useLocalization, Localization } from "../../components/Localization";

const parsePrice = (htmlString) => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(htmlString, 'text/html');
    return doc.body.textContent || "";
};

const StatusBadge = ({ status }) => {
    const getStatusColor = (status) => {
        switch (status) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'processing':
                return 'bg-blue-100 text-blue-800';
            case 'on-hold':
                return 'bg-yellow-100 text-yellow-800';
            case 'pending':
                return 'bg-orange-100 text-orange-800';
            case 'cancelled':
            case 'failed':
                return 'bg-red-100 text-red-800';
            case 'refunded':
                return 'bg-purple-100 text-purple-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getStatusLabel = (status) => {
        const labels = {
            'pending': Localization("orders.row.pending"),
            'processing': Localization("orders.row.processing"),
            'on-hold':  Localization("orders.row.on-hold"),
            'completed':  Localization("orders.row.completed"),
            'cancelled':  Localization("orders.row.cancelled"),
            'refunded':  Localization("orders.row.refunded"),
            'failed':  Localization("orders.row.failed"),
        };
        return labels[status] || status;
    };

    return (
        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(status)}`}>
            {getStatusLabel(status)}
        </span>
    );
};

const getCustomerName = (customer) => {
    return customer && customer.trim() !== '' ? customer : Localization("orders.row.guest");
};

const OrderRow = ({ order }) => {
    const isLocalizationLoaded = useLocalization();

    if (!order || !isLocalizationLoaded) {
        return null;
    }

    return (
        <tr>
            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <a href={`/wp-admin/post.php?post=${order.id}&action=edit`} target="_blank" rel="noopener noreferrer">
                    {order.id}
                </a>
            </td>
            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{getCustomerName(order.customer)}</td>
            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{order.date}</td>
            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{parsePrice(order.total)}</td>
            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <StatusBadge status={order.status} />
            </td>
        </tr>
    );
};

export default OrderRow;