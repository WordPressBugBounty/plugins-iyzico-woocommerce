// src/pages/Dashboard/Chart.js

import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';
import { Card, CardContent, CardHeader, CardTitle } from '../../components/Card';
import { useLocalization, Localization } from "../../components/Localization";

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#AF19FF'];

const Chart = ({ lastMonthOrders }) => {
    const isLocalizationLoaded = useLocalization();

    if (!lastMonthOrders || !isLocalizationLoaded) {
        return null;
    }

    const { ordersData, topProductsData, topCategoriesData } = lastMonthOrders;

    return (
        <div className="border-b border-gray-900/10 pb-12">
            <h2 className="text-base font-semibold leading-7 text-gray-900">
                {Localization("dashboard.chart.title")}
            </h2>
            <p className="mt-1 text-sm leading-6 text-gray-600">
                {Localization("dashboard.chart.description")}
            </p>
            <div className="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-1 lg:grid-cols-2">
                {/* 30 Günlük Sipariş Grafiği */}
                <Card>
                    <CardHeader>
                        <CardTitle>
                            {Localization("dashboard.charts.title_1")}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div style={{ width: '100%', height: 300 }}>
                            <ResponsiveContainer>
                                <LineChart data={ordersData}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="day" />
                                    <YAxis />
                                    <Tooltip />
                                    <Legend />
                                    <Line type="monotone" dataKey="total" name={Localization("dashboard.chart.keys.total")} stroke="#8884d8" />
                                    <Line type="monotone" dataKey="completed" name={Localization("dashboard.chart.keys.completed")} stroke="#82ca9d" />
                                    <Line type="monotone" dataKey="processing" name={Localization("dashboard.chart.keys.processing")} stroke="#ffc658" />
                                    <Line type="monotone" dataKey="pending" name={Localization("dashboard.chart.keys.pending")} stroke="#ff0000" />
                                    <Line type="monotone" dataKey="failed" name={Localization("dashboard.chart.keys.failed")} stroke="#000000" />
                                </LineChart>
                            </ResponsiveContainer>
                        </div>
                    </CardContent>
                </Card>

                {/* En Çok Satış Yapan İlk 5 Ürün */}
                <Card>
                    <CardHeader>
                        <CardTitle>
                            {Localization("dashboard.charts.title_3")}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div style={{ width: '100%', height: 300 }}>
                            <ResponsiveContainer>
                                <PieChart>
                                    <Pie
                                        data={topProductsData}
                                        cx="50%"
                                        cy="50%"
                                        outerRadius={100}
                                        fill="#8884d8"
                                        dataKey="value"
                                        label
                                    >
                                        {topProductsData.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                        ))}
                                    </Pie>
                                    <Tooltip />
                                    <Legend />
                                </PieChart>
                            </ResponsiveContainer>
                        </div>
                    </CardContent>
                </Card>

                {/* En Çok Satış Yapan İlk 5 Kategori */}
                <Card>
                    <CardHeader>
                        <CardTitle>
                            {Localization("dashboard.charts.title_4")}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div style={{ width: '100%', height: 300 }}>
                            <ResponsiveContainer>
                                <PieChart>
                                    <Pie
                                        data={topCategoriesData}
                                        cx="50%"
                                        cy="50%"
                                        outerRadius={100}
                                        fill="#82ca9d"
                                        dataKey="value"
                                        label
                                    >
                                        {topCategoriesData.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                        ))}
                                    </Pie>
                                    <Tooltip />
                                    <Legend />
                                </PieChart>
                            </ResponsiveContainer>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
};

export default Chart;