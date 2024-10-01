import OrderRow from "./OrderRow";
import { useLocalization, Localization } from "../../components/Localization";

const OrderTable = ({ orders }) => {

    const isLocalizationLoaded = useLocalization();

    if (!orders || !isLocalizationLoaded) {
        return null;
    }

    return (
        <div className="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
            <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                    <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {Localization("orders.row.id")}
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {Localization("orders.row.customer")}
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {Localization("orders.row.date")}
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {Localization("orders.row.total")}
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {Localization("orders.row.status")}
                        </th>
                    </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                    {orders.map((order) => (
                        <OrderRow key={order.id} order={order} />
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default OrderTable;