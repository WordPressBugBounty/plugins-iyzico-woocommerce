import { useState, useEffect } from '@wordpress/element';
import OrderTable from "./OrderTable";
import { getOrders } from '../../helper/api_helper';
import { useLocalization, Localization } from "../../components/Localization";

const Orders = () => {
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [orders, setOrders] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(0);
    const [isLoading, setIsLoading] = useState(true);

    const fetchOrders = async (page, search, status) => {
        setIsLoading(true);
        const data = await getOrders(page, 50, search, status);
        setOrders(data.orders);
        setTotalPages(data.total_pages);
        setCurrentPage(data.current_page);
        setIsLoading(false);
    };

    useEffect(() => {
        fetchOrders(1, searchTerm, statusFilter);
    }, [searchTerm, statusFilter]);

    const handlePageChange = (newPage) => {
        fetchOrders(newPage, searchTerm, statusFilter);
    };

    const handleSearch = (e) => {
        setSearchTerm(e.target.value);
        setCurrentPage(1);
    };

    const handleStatusChange = (e) => {
        setStatusFilter(e.target.value);
        setCurrentPage(1);
    };

    const isLocalizationLoaded = useLocalization();

    if (!isLocalizationLoaded) {
        return null;
    }

    return (
        <div className="border-b border-gray-900/10 pb-12">
            <h2 className="text-base font-semibold leading-7 text-gray-900">
                {Localization("orders.header")}
            </h2>
            <p className="mt-1 text-sm leading-6 text-gray-600">
                {Localization("orders.description")}
            </p>

            <div className="mb-6 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
                <input
                    type="text"
                    value={searchTerm}
                    onChange={handleSearch}
                    placeholder={Localization("orders.search_placeholder")}
                />
                <select
                    className="w-full appearance-none bg-white border border-gray-300 rounded-lg pl-4 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    value={statusFilter}
                    onChange={handleStatusChange}
                >
                    <option value="all">{Localization("orders.row.all")}</option>
                    <option value="completed">{Localization("orders.row.completed")}</option>
                    <option value="processing">{Localization("orders.row.processing")}</option>
                    <option value="pending">{Localization("orders.row.pending")}</option>
                    <option value="on-hold">{Localization("orders.row.on-hold")}</option>
                    <option value="cancelled">{Localization("orders.row.cancelled")}</option>
                    <option value="refunded">{Localization("orders.row.refunded")}</option>
                    <option value="failed">{Localization("orders.row.failed")}</option>
                </select>
            </div>

            {isLoading ? (
                <p>{Localization("orders.loading")}</p>
            ) : (
                <>
                    <OrderTable orders={orders} />
                    <div className="mt-4 flex justify-between">
                        <button
                            onClick={() => handlePageChange(currentPage - 1)}
                            disabled={currentPage === 1}
                        >
                            {Localization("orders.previous")}
                        </button>
                        <button
                            onClick={() => handlePageChange(currentPage + 1)}
                            disabled={currentPage === totalPages}
                        >
                            {Localization("orders.next")}
                        </button>
                    </div>
                </>
            )}
        </div>
    );
};

export default Orders;