import {useEffect, useState} from '@wordpress/element';
import Widget from './Widget';
import Chart from './Chart';

import {getSettingsDashboardWidgets, getSettingsDashboardCharts} from '../../helper/api_helper';

const Dashboard = () => {
    const [settingsDashboardWidgets, setSettingsDashboardWidgets] = useState(null);
    const [settingsDashboardCharts, setSettingsDashboardCharts] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const widgetsData = await getSettingsDashboardWidgets();
                setSettingsDashboardWidgets(widgetsData);

                const chartsData = await getSettingsDashboardCharts();
                setSettingsDashboardCharts(chartsData);
            } catch (error) {
                console.error('Veri çekme işlemi sırasında hata oluştu:', error);
            }
        };

        fetchData();
    }, []);

    return (
        <div className="space-y-12">
            <Widget stats={settingsDashboardWidgets}/>
            <Chart lastMonthOrders={settingsDashboardCharts}/>
        </div>
    );
};

export default Dashboard;