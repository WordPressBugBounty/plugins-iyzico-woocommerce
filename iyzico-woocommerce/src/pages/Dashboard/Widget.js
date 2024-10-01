import StatCard from "../../components/StatCard";
import { useLocalization, Localization } from "../../components/Localization";

const Widget = ({ stats }) => {
    const isLocalizationLoaded = useLocalization();

    if (!stats || !isLocalizationLoaded) {
        return null;
    }

    return (
        <div className="border-b border-gray-900/10 pb-12">
            <h2 className="text-base font-semibold leading-7 text-gray-900">
                {Localization("dashboard.widget.title")}
            </h2>
            <p className="mt-1 text-sm leading-6 text-gray-600">
                {Localization("dashboard.widget.description")}
            </p>
            <div className="mt-10 space-y-10">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {stats.map((stat, index) => (
                        <StatCard
                            key={index}
                            title={stat.title}
                            value={stat.value}
                            icon={stat.icon}
                        />
                    ))}
                </div>
            </div>
        </div>
    );
};

export default Widget;