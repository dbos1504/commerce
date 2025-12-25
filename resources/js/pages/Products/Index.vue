<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ShoppingCart, Package } from 'lucide-vue-next';
import { ref } from 'vue';

interface Product {
    id: number;
    name: string;
    price: string;
    stock_quantity: number;
}

interface Props {
    products: Product[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '/products',
    },
];

const addingToCart = ref<number | null>(null);

const addToCart = (productId: number) => {
    addingToCart.value = productId;
    router.post('/cart/add', {
        product_id: productId,
        quantity: 1,
    }, {
        preserveScroll: true,
        onFinish: () => {
            addingToCart.value = null;
        },
    });
};

const formatPrice = (price: string | number): string => {
    const numPrice = typeof price === 'string' ? parseFloat(price) : price;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(numPrice);
};

const isLowStock = (stock: number): boolean => {
    return stock <= 10;
};
</script>

<template>
    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Products</h1>
                    <p class="text-muted-foreground">Browse our collection of products</p>
                </div>
            </div>

            <div v-if="products.length === 0" class="flex flex-col items-center justify-center py-12">
                <Package class="h-12 w-12 text-muted-foreground mb-4" />
                <p class="text-muted-foreground">No products available</p>
            </div>

            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card v-for="product in products" :key="product.id" class="flex flex-col">
                    <CardHeader>
                        <CardTitle class="line-clamp-2">{{ product.name }}</CardTitle>
                        <CardDescription>
                            <div class="flex items-center gap-2 mt-2">
                                <Badge v-if="isLowStock(product.stock_quantity)" variant="destructive">
                                    Low Stock
                                </Badge>
                                <Badge v-else-if="product.stock_quantity === 0" variant="outline">
                                    Out of Stock
                                </Badge>
                                <span v-else class="text-sm text-muted-foreground">
                                    {{ product.stock_quantity }} in stock
                                </span>
                            </div>
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex-1">
                        <p class="text-2xl font-bold">{{ formatPrice(product.price) }}</p>
                    </CardContent>
                    <CardFooter>
                        <Button
                            :disabled="product.stock_quantity === 0 || addingToCart === product.id"
                            @click="addToCart(product.id)"
                            class="w-full"
                            variant="default"
                        >
                            <ShoppingCart v-if="addingToCart !== product.id" class="mr-2 h-4 w-4" />
                            <span v-if="addingToCart === product.id">Adding...</span>
                            <span v-else>Add to Cart</span>
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

