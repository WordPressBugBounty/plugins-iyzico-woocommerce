const Sidebar = () => {
    return (
        <aside className="w-64 bg-white shadow-lg rounded">
            <nav className="p-4">
                <ul>
                    <li className="mb-2">
                        <a href="#" className="block p-2 rounded bg-gray-200">Dashboard</a>
                    </li>
                    <li className="mb-2">
                        <a href="#" className="block p-2 rounded hover:bg-gray-200">Orders</a>
                    </li>
                    <li className="mb-2">
                        <a href="#" className="block p-2 rounded hover:bg-gray-200">Settings</a>
                    </li>
                </ul>
            </nav>
        </aside>
    )
}
export default Sidebar