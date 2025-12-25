<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Trash2, ShoppingCart, Plus, Minus, CreditCard } from 'lucide-vue-next';
import { ref } from 'vue';

interface Product {
    id: number;
    name: string;
    price: string;
    stock_quantity: number;
}

interface CartItem {
    id: number;
    product_id: number;
    quantity: number;
    product: Product;
}

interface Cart {
    id: number;
    user_id: number;
    items: CartItem[];
    total: string;
}

interface Props {
    cart: Cart;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Shopping Cart',
        href: '/cart',
    },
];

const updating = ref<number | null>(null);
const quantities = ref<Record<number, number>>({});

// Initialize quantities from cart items
props.cart.items.forEach(item => {
    quantities.value[item.id] = item.quantity;
});

const formatPrice = (price: string | number): string => {
    const numPrice = typeof price === 'string' ? parseFloat(price) : price;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(numPrice);
};

const updateQuantity = (itemId: number, newQuantity: number) => {
    if (newQuantity < 1) {
        removeItem(itemId);
        return;
    }

    updating.value = itemId;
    quantities.value[itemId] = newQuantity;

    router.put(`/cart/${itemId}`, {
        quantity: newQuantity,
    }, {
        preserveScroll: true,
        onFinish: () => {
            updating.value = null;
        },
        onError: () => {
            // Revert quantity on error
            quantities.value[itemId] = props.cart.items.find(i => i.id === itemId)?.quantity || 1;
        },
    });
};

const incrementQuantity = (item: CartItem) => {
    const newQuantity = quantities.value[item.id] + 1;
    if (newQuantity <= item.product.stock_quantity) {
        updateQuantity(item.id, newQuantity);
    }
};

const decrementQuantity = (item: CartItem) => {
    const newQuantity = quantities.value[item.id] - 1;
    updateQuantity(item.id, newQuantity);
};

const removeItem = (itemId: number) => {
    router.delete(`/cart/${itemId}`, {
        preserveScroll: true,
    });
};

const checkingOut = ref(false);

const checkout = () => {
    checkingOut.value = true;
    router.post('/cart/checkout', {}, {
        preserveScroll: true,
        onFinish: () => {
            checkingOut.value = false;
        },
    });
};
</script>

<template>
    <Head title="Shopping Cart" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Shopping Cart</h1>
                    <p class="text-muted-foreground">Review and manage your cart items</p>
                </div>
            </div>

            <div v-if="cart.items.length === 0" class="flex flex-col items-center justify-center py-12">
                <ShoppingCart class="h-12 w-12 text-muted-foreground mb-4" />
                <p class="text-muted-foreground mb-4">Your cart is empty</p>
                <Button as-child>
                    <a href="/products">Browse Products</a>
                </Button>
            </div>

            <div v-else class="grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-4">
                    <Card v-for="item in cart.items" :key="item.id">
                        <CardHeader>
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <CardTitle>{{ item.product.name }}</CardTitle>
                                    <CardDescription class="mt-1">
                                        {{ formatPrice(item.product.price) }} each
                                    </CardDescription>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="removeItem(item.id)"
                                    class="text-destructive hover:text-destructive"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        @click="decrementQuantity(item)"
                                        :disabled="updating === item.id || quantities[item.id] <= 1"
                                    >
                                        <Minus class="h-4 w-4" />
                                    </Button>
                                    <Input
                                        :model-value="quantities[item.id]"
                                        @update:model-value="(val) => updateQuantity(item.id, parseInt(val) || 1)"
                                        type="number"
                                        min="1"
                                        :max="item.product.stock_quantity"
                                        class="w-20 text-center"
                                        :disabled="updating === item.id"
                                    />
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        @click="incrementQuantity(item)"
                                        :disabled="updating === item.id || quantities[item.id] >= item.product.stock_quantity"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold">
                                        {{ formatPrice(quantities[item.id] * parseFloat(item.product.price)) }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ quantities[item.id] }} × {{ formatPrice(item.product.price) }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="item.product.stock_quantity < quantities[item.id]" class="mt-2">
                                <Badge variant="destructive" class="text-xs">
                                    Only {{ item.product.stock_quantity }} available
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="lg:col-span-1">
                    <Card>
                        <CardHeader>
                            <CardTitle>Order Summary</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span>{{ formatPrice(cart.total) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Items</span>
                                <span>{{ cart.items.length }}</span>
                            </div>
                            <div class="border-t pt-4">
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total</span>
                                    <span>{{ formatPrice(cart.total) }}</span>
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter>
                            <Button
                                @click="checkout"
                                :disabled="checkingOut"
                                class="w-full"
                                size="lg"
                            >
                                <CreditCard v-if="!checkingOut" class="mr-2 h-4 w-4" />
                                <span v-if="checkingOut">Processing...</span>
                                <span v-else>Checkout</span>
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

