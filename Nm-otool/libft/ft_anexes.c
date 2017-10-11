/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_anexes.c                                        :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/17 20:36:35 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/09 14:30:27 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

char	*ft_copyy(char *s, char *base)
{
	int		tall;
	size_t	i;

	i = 0;
	tall = ft_strlen(s) - 1;
	while (tall >= 0)
	{
		if (s[tall] == 'x')
		{
			if (i < ft_strlen(base))
				s[tall] = base[i];
			else
				s[tall] = '0';
			i++;
		}
		tall--;
	}
	return (s);
}

char	*ft_chainz(char **mask, int i)
{
	char				*buf;
	int					y;
	unsigned int		nb;
	int					x;

	while (mask[i] != '\0')
		i++;
	buf = ft_strnew(i + 1);
	i--;
	while (i >= 0)
	{
		y = 7;
		nb = 0;
		x = 1;
		while (y >= 0)
		{
			if (mask[i][y] == '1')
				nb = nb + x;
			x *= 2;
			y--;
		}
		buf[i] = nb;
		i--;
	}
	return (buf);
}

char	*ft_spec(char *str)
{
	char	*buf;
	char	*mask;
	int		i;

	i = 0;
	if (ft_strlen(ft_reverse(str)) <= 7)
		buf = ft_chainz(ft_strsplit(ft_copyy(ft_strdup("0xxxxxxx")\
						, ft_reverse(str)), ' '), i);
	else if (ft_strlen(ft_reverse(str)) <= 11)
	{
		mask = ft_strdup("110xxxxx 10xxxxxx");
		buf = ft_chainz(ft_strsplit(ft_copyy(mask, ft_reverse(str)), ' '), i);
	}
	else if (ft_strlen(ft_reverse(str)) <= 16)
	{
		mask = ft_strdup("1110xxxx 10xxxxxx 10xxxxxx");
		buf = ft_chainz(ft_strsplit(ft_copyy(mask, ft_reverse(str)), ' '), i);
	}
	else
	{
		mask = ft_strdup("11110xxx 10xxxxxx 10xxxxxx 10xxxxxx");
		buf = ft_chainz(ft_strsplit(ft_copyy(mask, ft_reverse(str)), ' '), i);
	}
	return (buf);
}

char	*ft_precision(const char *str, int i, t_char *all, t_chain *ap)
{
	char	*preci;

	preci = ft_strnew(1);
	if (ft_presence_star(str, i) == 1 || ft_presence_star(str, i) == 3)
	{
		g_preci = ft_dolls(str, i, ap, all);
		if (ft_presence_star(str, i) == 1)
			*ap = *ap->next;
	}
	if (str[i] == 'p')
		return (ft_preci_for_p(all->arg));
	else if (g_preci == -10)
		return (all->arg);
	if (str[i] == 's' || str[i] == 'S')
		ft_precision_bis(str, i, &all->arg, &preci);
	else if (str[i] == 'd' || str[i] == 'i' || str[i] == 'D' \
			|| str[i] == 'o' || str[i] == 'O' \
			|| str[i] == 'u' || str[i] == 'U' || str[i] == 'x' || \
			str[i] == 'X' || str[i] == 'b')
		if ((g_preci == 0 && all->arg && all->arg[0] == '0') || \
				(g_preci > (int)ft_strlen(all->arg)))
			return (ft_preci_other(all, i, &all->arg, &preci));
	return (all->arg);
}

char	*ft_final(t_char *all, int *tab, char c)
{
	if (tab[1] == 1 && c == 'p' && ft_strcmp(all->arg, ft_strdup("0x0")) == 0)
		return (ft_strjoin(ft_strjoin(all->hexa, all->preci), all->large));
	if (all->large[0] == '\0')
		return (ft_strjoin(all->hexa, all->preci));
	if (ft_strchr(all->large, '0') != 0)
		return (ft_strjoin(all->hexa, ft_strjoin(all->large, all->preci)));
	if (tab[2] == 1)
		return (ft_strjoin(ft_strjoin(all->hexa, all->preci), all->large));
	return (ft_strjoin(all->large, ft_strjoin(all->hexa, all->preci)));
}
