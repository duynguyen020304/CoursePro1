// Cart Context with TypeScript
// Provides shopping cart state and operations

import {
  createContext,
  useCallback,
  useContext,
  type ReactNode,
} from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { cartApi } from '../services/api';
import type { Cart } from '../schemas/cart/apiResponses.schema';
import { useAuth } from './AuthContext';

/**
 * Cart context state
 */
interface CartState {
  cart: Cart | null;
  loading: boolean;
  initialized: boolean;
  items: Cart['items'] | [];
  itemCount: number;
}

/**
 * Cart context value shape with all methods
 */
interface CartContextValue extends CartState {
  fetchCart: () => Promise<void>;
  addItem: (courseId: string, quantity?: number) => Promise<{ success: boolean; data?: unknown; message?: string }>;
  removeItem: (cartItemId: string) => Promise<{ success: boolean; message?: string }>;
  clearCart: () => Promise<{ success: boolean; message?: string }>;
}

/**
 * Cart context with nullable initial value
 */
const CartContext = createContext<CartContextValue | null>(null);

/**
 * Props for CartProvider
 */
interface CartProviderProps {
  children: ReactNode;
}

/**
 * Cart Provider component
 * Manages shopping cart state including items, loading, and operations
 */
export function CartProvider({ children }: CartProviderProps) {
  const queryClient = useQueryClient();
  const { isAuthenticated, loading: authLoading, user } = useAuth();
  const cartQueryKey = ['cart', user?.user_id ?? 'guest'] as const;
  const cartQuery = useQuery({
    queryKey: cartQueryKey,
    queryFn: async () => {
      try {
        const response = await cartApi.get();
        return response.data.data as Cart | null;
      } catch (error) {
        const errorResponse = error as { response?: { status?: number } };
        if (errorResponse.response?.status === 401) {
          return null;
        }

        throw error;
      }
    },
    enabled: !authLoading && isAuthenticated,
    retry: false,
    refetchOnWindowFocus: false,
  });

  /**
   * Fetches the current cart from the API
   */
  const fetchCart = useCallback(async (): Promise<void> => {
    if (!isAuthenticated) {
      queryClient.removeQueries({ queryKey: ['cart'] });
      return;
    }

    await queryClient.invalidateQueries({ queryKey: cartQueryKey, exact: true });
  }, [cartQueryKey, isAuthenticated, queryClient]);

  const addItemMutation = useMutation({
    mutationFn: async ({ courseId, quantity }: { courseId: string; quantity: number }) => {
      return cartApi.addItem(courseId, quantity);
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: cartQueryKey, exact: true });
    },
  });

  const removeItemMutation = useMutation({
    mutationFn: async (cartItemId: string) => {
      return cartApi.removeItem(cartItemId);
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: cartQueryKey, exact: true });
    },
  });

  const clearCartMutation = useMutation({
    mutationFn: async () => {
      return cartApi.clear();
    },
    onSuccess: () => {
      queryClient.setQueryData(cartQueryKey, null);
    },
  });

  /**
   * Adds an item to the cart
   */
  const addItem = useCallback(
    async (courseId: string, quantity = 1): Promise<{ success: boolean; data?: unknown; message?: string }> => {
      try {
        const response = await addItemMutation.mutateAsync({ courseId, quantity });
        return { success: true, data: response };
      } catch (error) {
        const errorResponse = error as { response?: { data?: { message?: string } } };
        return {
          success: false,
          message: errorResponse.response?.data?.message || 'Failed to add to cart',
        };
      }
    },
    [addItemMutation]
  );

  /**
   * Removes an item from the cart
   */
  const removeItem = useCallback(
    async (cartItemId: string): Promise<{ success: boolean; message?: string }> => {
      try {
        await removeItemMutation.mutateAsync(cartItemId);
        return { success: true };
      } catch (error) {
        const errorResponse = error as { response?: { data?: { message?: string } } };
        return {
          success: false,
          message: errorResponse.response?.data?.message || 'Failed to remove from cart',
        };
      }
    },
    [removeItemMutation]
  );

  /**
   * Clears all items from the cart
   */
  const clearCart = useCallback(async (): Promise<{ success: boolean; message?: string }> => {
    try {
      await clearCartMutation.mutateAsync();
      return { success: true };
    } catch (error) {
      const errorResponse = error as { response?: { data?: { message?: string } } };
      return {
        success: false,
        message: errorResponse.response?.data?.message || 'Failed to clear cart',
      };
    }
  }, [clearCartMutation]);

  const cart = isAuthenticated ? cartQuery.data ?? null : null;
  const loading = !authLoading && isAuthenticated && (cartQuery.isPending || cartQuery.isFetching);
  const initialized = !authLoading && (!isAuthenticated || cartQuery.isFetched || cartQuery.isError);

  const value: CartContextValue = {
    cart,
    loading,
    initialized,
    items: cart?.items || [],
    itemCount: cart?.items?.length || 0,
    fetchCart,
    addItem,
    removeItem,
    clearCart,
  };

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

/**
 * Hook to access cart context
 * @throws Error if used outside of CartProvider
 */
export function useCart(): CartContextValue {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
}
