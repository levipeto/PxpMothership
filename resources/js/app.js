import * as echarts from 'echarts';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Make echarts available globally
window.echarts = echarts;
window.L = L;

// Register Alpine components when Alpine is ready
document.addEventListener('alpine:init', () => {
    // Reusable ECharts component
    Alpine.data('echart', (initialOptions = {}) => ({
        chart: null,
        options: initialOptions,

        init() {
            this.$nextTick(() => {
                this.initChart();
            });

            // Handle theme changes
            const observer = new MutationObserver(() => {
                if (this.chart) {
                    this.chart.dispose();
                    this.initChart();
                }
            });
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class'],
            });

            // Handle resize
            const resizeHandler = () => {
                this.chart?.resize();
            };
            window.addEventListener('resize', resizeHandler);

            // Cleanup on destroy
            this.$cleanup = () => {
                observer.disconnect();
                window.removeEventListener('resize', resizeHandler);
                this.chart?.dispose();
            };
        },

        initChart() {
            if (!this.$refs.chart) {
                return;
            }

            const isDark = document.documentElement.classList.contains('dark');
            this.chart = echarts.init(this.$refs.chart, isDark ? 'dark' : null, {
                renderer: 'canvas',
            });

            // Apply base styling for dark mode
            const baseOptions = {
                backgroundColor: 'transparent',
                textStyle: {
                    fontFamily: 'inherit',
                },
                ...this.options,
            };

            this.chart.setOption(baseOptions);
        },

        updateOptions(newOptions) {
            if (this.chart) {
                this.chart.setOption(newOptions, { notMerge: false });
            }
        },

        replaceOptions(newOptions) {
            this.options = newOptions;
            if (this.chart) {
                this.chart.setOption(newOptions, { notMerge: true });
            }
        },

        destroy() {
            this.$cleanup?.();
        },
    }));

    // Yearly comparison chart with custom tooltip
    Alpine.data('yearlyComparisonChart', (options) => ({
        chart: null,
        courierInfo: options.courierInfo || {},

        init() {
            this.$nextTick(() => {
                this.initChart();
            });

            const observer = new MutationObserver(() => {
                if (this.chart) {
                    this.chart.dispose();
                    this.initChart();
                }
            });
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class'],
            });

            window.addEventListener('resize', () => this.chart?.resize());
        },

        initChart() {
            if (!this.$refs.chart) {
                return;
            }

            const isDark = document.documentElement.classList.contains('dark');
            this.chart = echarts.init(this.$refs.chart, isDark ? 'dark' : null);

            const courierInfo = this.courierInfo;

            const chartOptions = {
                backgroundColor: 'transparent',
                ...options,
                tooltip: {
                    trigger: 'item',
                    confine: true,
                    formatter: function (params) {
                        const seriesName = params.seriesName;
                        const info = courierInfo[seriesName] || {};
                        const code = info.code || seriesName;
                        const fullName = info.name || '-';
                        const month = params.name;
                        const value = params.value;

                        return `<div style="padding: 8px;">
                            <div style="font-weight: bold; font-size: 14px; margin-bottom: 4px;">${code}</div>
                            <div style="color: #666; margin-bottom: 8px;">${fullName}</div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 10px; height: 10px; background: ${params.color}; border-radius: 50%;"></span>
                                <span>${month}: <strong>${value}</strong> rendelés</span>
                            </div>
                        </div>`;
                    },
                },
            };

            this.chart.setOption(chartOptions);
        },
    }));

    // Weekly trend chart with click handler
    Alpine.data('weeklyTrendChart', (options, onWeekClick) => ({
        chart: null,

        init() {
            this.$nextTick(() => {
                this.initChart();
            });

            const observer = new MutationObserver(() => {
                if (this.chart) {
                    this.chart.dispose();
                    this.initChart();
                }
            });
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class'],
            });

            window.addEventListener('resize', () => this.chart?.resize());
        },

        initChart() {
            if (!this.$refs.chart) {
                return;
            }

            const isDark = document.documentElement.classList.contains('dark');
            this.chart = echarts.init(this.$refs.chart, isDark ? 'dark' : null);

            const chartOptions = {
                backgroundColor: 'transparent',
                ...options,
            };

            this.chart.setOption(chartOptions);

            // Handle click on bars
            this.chart.on('click', (params) => {
                if (params.componentType === 'series' && onWeekClick) {
                    this.$wire.call(onWeekClick, params.name);
                }
            });
        },

        updateOptions(newOptions) {
            if (this.chart) {
                this.chart.setOption(newOptions, { notMerge: false });
            }
        },
    }));

    // Courier Coverage Map with Leaflet
    Alpine.data('courierCoverageMap', (config) => ({
        map: null,
        coverageData: config.coverageData || [],
        postalCodeLookup: config.postalCodeLookup || {},
        selectedCourier: config.selectedCourier,
        budapestGeoJSON: null,
        postalCentroids: null,
        layers: [],

        async init() {
            await this.$nextTick();
            await this.loadData();
            this.initMap();
        },

        async loadData() {
            try {
                const [geoResponse, centroidsResponse] = await Promise.all([
                    fetch('/data/budapest-postal-codes.geojson'),
                    fetch('/data/hungary-postal-centroids.json'),
                ]);
                this.budapestGeoJSON = await geoResponse.json();
                this.postalCentroids = await centroidsResponse.json();
            } catch (error) {
                console.error('Failed to load map data:', error);
            }
        },

        initMap() {
            const isDark = document.documentElement.classList.contains('dark');

            this.map = L.map(this.$refs.map, {
                center: [47.1625, 19.5033],
                zoom: 7,
                zoomControl: true,
            });

            // Add tile layer
            const tileUrl = isDark
                ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
                : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';

            L.tileLayer(tileUrl, {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 19,
            }).addTo(this.map);

            this.renderCoverage();

            // Watch for theme changes
            const observer = new MutationObserver(() => {
                this.map.eachLayer((layer) => {
                    if (layer instanceof L.TileLayer) {
                        this.map.removeLayer(layer);
                    }
                });
                const newTileUrl = document.documentElement.classList.contains('dark')
                    ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
                    : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
                L.tileLayer(newTileUrl, {
                    attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                    subdomains: 'abcd',
                    maxZoom: 19,
                }).addTo(this.map);
            });
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class'],
            });
        },

        renderCoverage() {
            // Clear existing layers
            this.layers.forEach((layer) => this.map.removeLayer(layer));
            this.layers = [];

            // Create a map of postal code to courier color
            const postalCodeColors = {};
            this.coverageData.forEach((courier) => {
                if (this.selectedCourier && courier.courier_code !== this.selectedCourier) {
                    return;
                }
                courier.postal_codes.forEach((code) => {
                    if (!postalCodeColors[code]) {
                        postalCodeColors[code] = [];
                    }
                    postalCodeColors[code].push({
                        code: courier.courier_code,
                        name: courier.courier_name,
                        color: courier.color,
                    });
                });
            });

            // Render Budapest polygons
            if (this.budapestGeoJSON) {
                const budapestLayer = L.geoJSON(this.budapestGeoJSON, {
                    style: (feature) => {
                        const postalCode = feature.properties.postal_code?.toString();
                        const couriers = postalCodeColors[postalCode];
                        if (couriers && couriers.length > 0) {
                            return {
                                fillColor: couriers[0].color,
                                fillOpacity: 0.5,
                                color: couriers[0].color,
                                weight: 2,
                            };
                        }
                        return {
                            fillColor: '#gray',
                            fillOpacity: 0.1,
                            color: '#ccc',
                            weight: 1,
                        };
                    },
                    onEachFeature: (feature, layer) => {
                        const postalCode = feature.properties.postal_code?.toString();
                        const couriers = postalCodeColors[postalCode];
                        if (couriers && couriers.length > 0) {
                            const popupContent = `
                                <div style="min-width: 150px;">
                                    <strong>${postalCode}</strong><br/>
                                    ${couriers
                                        .map(
                                            (c) =>
                                                `<span style="color: ${c.color};">&#9679;</span> ${c.code}${c.name ? ` (${c.name})` : ''}`
                                        )
                                        .join('<br/>')}
                                </div>
                            `;
                            layer.bindPopup(popupContent);
                        }
                    },
                });
                budapestLayer.addTo(this.map);
                this.layers.push(budapestLayer);
            }

            // Render non-Budapest areas as circles
            if (this.postalCentroids) {
                const budapestCodes = new Set();
                if (this.budapestGeoJSON) {
                    this.budapestGeoJSON.features.forEach((f) => {
                        budapestCodes.add(f.properties.postal_code?.toString());
                    });
                }

                this.postalCentroids.forEach((centroid) => {
                    const postalCode = centroid.postal_code;
                    if (budapestCodes.has(postalCode)) return;

                    const couriers = postalCodeColors[postalCode];
                    if (!couriers || couriers.length === 0) return;

                    const circle = L.circleMarker([centroid.lat, centroid.lng], {
                        radius: 6,
                        fillColor: couriers[0].color,
                        fillOpacity: 0.7,
                        color: couriers[0].color,
                        weight: 1,
                    });

                    const popupContent = `
                        <div style="min-width: 150px;">
                            <strong>${postalCode}</strong> - ${centroid.name}<br/>
                            ${couriers
                                .map(
                                    (c) =>
                                        `<span style="color: ${c.color};">&#9679;</span> ${c.code}${c.name ? ` (${c.name})` : ''}`
                                )
                                .join('<br/>')}
                        </div>
                    `;
                    circle.bindPopup(popupContent);
                    circle.addTo(this.map);
                    this.layers.push(circle);
                });
            }
        },

        handleCourierSelection(courierCode) {
            this.selectedCourier = courierCode;
            this.renderCoverage();

            // Zoom to courier's coverage if selected
            if (courierCode) {
                const courier = this.coverageData.find((c) => c.courier_code === courierCode);
                if (courier && courier.postal_codes.length > 0) {
                    const bounds = [];
                    courier.postal_codes.forEach((code) => {
                        const centroid = this.postalCentroids?.find((c) => c.postal_code === code);
                        if (centroid) {
                            bounds.push([centroid.lat, centroid.lng]);
                        }
                    });
                    if (bounds.length > 0) {
                        this.map.fitBounds(bounds, { padding: [50, 50] });
                    }
                }
            } else {
                this.map.setView([47.1625, 19.5033], 7);
            }
        },
    }));
});
