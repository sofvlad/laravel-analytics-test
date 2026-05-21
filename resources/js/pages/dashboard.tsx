import { Head, usePage } from '@inertiajs/react';
import VisitCharts from '@/components/visit-charts';
import { dashboard } from '@/routes';

const embedCode = `(async function() {
    const serverUrl = 'YOUR_SERVER_URL/api/v1/visits/track';

    async function sendTrackRequest(data = {}) {
        try {
            const response = await fetch(serverUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                console.log('Запрос успешно отправлен на', serverUrl);
                return true;
            } else {
                throw new Error(\`Ошибка отправки данных: HTTP \${response.status}\`);
            }
        } catch (error) {
            console.error('Ошибка отправки запроса:', error.message);
            return false;
        }
    }

    try {
        if (!navigator.geolocation) {
            console.warn('Геолокация не поддерживается, отправляем запрос без параметров');
            await sendTrackRequest();
            return;
        }

        console.log('Запрашиваем разрешение на использование геолокации...');

        const position = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            });
        });

        const { latitude, longitude } = position.coords;

        console.log('Получены координаты:');
        console.log(\`Широта: \${latitude.toFixed(6)}\`);
        console.log(\`Долгота: \${longitude.toFixed(6)}\`);

        await sendTrackRequest({
            lat: latitude,
            lon: longitude
        });
    } catch (error) {
        console.warn('Не удалось получить геолокацию, отправляем запрос без параметров');
        await sendTrackRequest();
    }
})();
`;

export default function Dashboard() {
    const { appUrl } = usePage().props;
    const generatedCode = embedCode.replace('YOUR_SERVER_URL', appUrl);

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <VisitCharts />

                <div className="rounded-lg border bg-card p-6">
                    <h2 className="mb-4 text-xl font-semibold">
                        Код для встраивания
                    </h2>
                    <p className="mb-4 text-sm text-muted-foreground">
                        Добавьте этот код на свой сайт для отслеживания
                        посещений с геолокацией. Скопируйте код выше и добавьте
                        его в{' '}
                        <code className="rounded bg-muted px-1 py-0.5">
                            &lt;script&gt;
                        </code>{' '}
                        тег на вашем сайте.
                    </p>
                    <pre className="overflow-x-auto rounded-lg bg-muted p-4 text-sm">
                        <code>{generatedCode}</code>
                    </pre>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
