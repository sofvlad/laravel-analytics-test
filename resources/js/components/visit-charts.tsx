import { useEffect, useState } from 'react';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import { Line, Doughnut } from 'react-chartjs-2';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
);

interface HourlyStats {
    labels: string[];
    data: number[];
}

interface CityStats {
    labels: string[];
    data: number[];
}

export default function VisitCharts() {
    const [hourlyStats, setHourlyStats] = useState<HourlyStats>({ labels: [], data: [] });
    const [cityStats, setCityStats] = useState<CityStats>({ labels: [], data: [] });
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchStats = async () => {
            try {
                const [hourlyResponse, cityResponse] = await Promise.all([
                    fetch('/visits/hourly-stats', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }),
                    fetch('/visits/city-stats', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }),
                ]);

                if (hourlyResponse.ok) {
                    const hourlyData = await hourlyResponse.json();
                    setHourlyStats(hourlyData);
                }

                if (cityResponse.ok) {
                    const cityData = await cityResponse.json();
                    setCityStats(cityData);
                }
            } catch (error) {
                console.error('Failed to fetch stats:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchStats();
    }, []);

    const hourlyChartData = {
        labels: hourlyStats.labels.map(label => {
            const date = new Date(label);

            return date.toLocaleString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            });
        }),
        datasets: [
            {
                label: 'Уникальные посещения',
                data: hourlyStats.data,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                tension: 0.3,
            },
        ],
    };

    const hourlyChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top' as const,
            },
            title: {
                display: true,
                text: 'Посещения по часам',
            },
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Время',
                },
            },
            y: {
                title: {
                    display: true,
                    text: 'Количество посещений',
                },
                beginAtZero: true,
            },
        },
    };

    const cityChartData = {
        labels: cityStats.labels,
        datasets: [
            {
                label: 'Посещения',
                data: cityStats.data,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(199, 199, 199, 0.8)',
                    'rgba(83, 102, 255, 0.8)',
                    'rgba(255, 99, 255, 0.8)',
                    'rgba(99, 255, 132, 0.8)',
                ],
                borderColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 206, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)',
                    'rgb(255, 159, 64)',
                    'rgb(199, 199, 199)',
                    'rgb(83, 102, 255)',
                    'rgb(255, 99, 255)',
                    'rgb(99, 255, 132)',
                ],
                borderWidth: 1,
            },
        ],
    };

    const cityChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right' as const,
            },
            title: {
                display: true,
                text: 'Посещения по городам',
            },
        },
    };

    return (
        <div className="grid gap-4 md:grid-cols-2">
            <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 p-4 pb-8 dark:border-sidebar-border">
                <h3 className="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Посещения по часам</h3>
                {loading ? (
                    <div className="flex h-full items-center justify-center">
                        <div className="text-sm text-gray-500">Загрузка...</div>
                    </div>
                ) : (
                    <Line data={hourlyChartData} options={hourlyChartOptions} />
                )}
            </div>
            <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 p-4 pb-8 dark:border-sidebar-border">
                <h3 className="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Посещения по городам</h3>
                {loading ? (
                    <div className="flex h-full items-center justify-center">
                        <div className="text-sm text-gray-500">Загрузка...</div>
                    </div>
                ) : (
                    <Doughnut data={cityChartData} options={cityChartOptions} />
                )}
            </div>
        </div>
    );
}
