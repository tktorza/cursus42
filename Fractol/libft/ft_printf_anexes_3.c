/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_printf_anexes_3.c                               :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/23 16:25:37 by tktorza           #+#    #+#             */
/*   Updated: 2016/02/27 15:20:58 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

int		ft_modif(const char *s, int i)
{
	if (s[i] == 'D' || s[i] == 'S' || (s[i - 1] == 'l' && (s[i] == 'c' ||\
					s[i] == 'C')))
		return (3);
	else if (s[i] == 'O' || s[i] == 'U')
		return (7);
	else if (s[i - 1] == 'h')
	{
		if (s[i - 2] == 'h')
			return (2);
		return (1);
	}
	else if (s[i - 1] == 'l')
	{
		if (s[i - 2] == 'l')
			return (4);
		return (3);
	}
	else if (s[i - 1] == 'j')
		return (5);
	else if (s[i - 1] == 'z')
		return (6);
	else
		return (0);
}

char	*ft_arg(const char *s, int *index, va_list *ap)
{
	char				*ref;
	char				*result;
	int					i;
	static t_fun_ptr	tab[15];
	int					modif;

	result = NULL;
	i = 0;
	modif = ft_modif(s, *index);
	ref = ft_strdup("sdciouxOUXDpCSb");
	while (ft_strchr(ref, s[*index]) == 0)
		*index += 1;
	ft_create_tab(tab);
	while (ref[i])
	{
		if (s[*index] == ref[i])
			result = tab[i](ap, modif);
		i++;
	}
	return (result);
}

void	ft_clean(int *tab, int size)
{
	tab[size] = -10;
	size--;
	tab[size] = -10;
	size--;
	tab[size] = -10;
	size--;
	while (size >= 0)
	{
		tab[size] = 0;
		size--;
	}
}
